<?php

namespace App\Controller\Dashboard;

use App\Entity\Report;
use App\Form\User1Type;
use App\Form\UserType;
use App\Repository\ReportRepository;
use App\Service\GoogleAnalyticsService;
use App\Service\Yegob_WP_Service;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\ItemInterface;

#[Route('/dashboard', name: 'app_dashboard')]
class DashboardController extends AbstractController
{

    #[Route('/', name: '_index')]
    public function index(
        Request $request,
        ReportRepository $repository
    ): Response
    {
        $user = $this->getUser();
        $selectedYear = $request->get('year', date('Y'));
        $selectedMonth = $request->get('month', date('m'));

        $reports = $repository->findReportsMonthly(
            $user->getId(),
            $selectedMonth,
            $selectedYear
        );

        $totalUniquePageViews = array_reduce(
            array_map(fn($report) => $report->getUniquePageviews(), $reports),
            fn($x, $y) => $x + $y,
            0
        );


        $totalPageViews = array_reduce(
            array_map(fn($report) => $report->getPageviews(), $reports),
            fn($x, $y) => $x + $y,
            0
        );

        $reportsByDatePeriod = [];

        // dd($reports);

        return $this->render('dashboard/index.html.twig', [
            'user' => $user,
            'isAdmin' => $user->isAdmin(),
            'reports' => $reports,
            'currentUniquePageViews' => $reports != [] ? end($reports)->getUniquePageviews() : 0,
            'totalPageviews' => $totalPageViews,
            'totalUniquePageViews' => $totalUniquePageViews
        ]);
    }

    #[Route('/links', name: '_links_page')]
    public function showArticles(
        Yegob_WP_Service $wp
    )
    {
        $cache = new FilesystemAdapter();
        $articles = $cache->get(
            'cache_article_contents',
            function(ItemInterface $item) use ($wp) {
                $item->expiresAfter(3600);
                return $wp->getPostsFromWP();
            });

        $user = $this->getUser();
        return $this->render('dashboard/articles.html.twig', [
            'articles' => $articles,
            'user' => $user,
            'isAdmin' => $user->isAdmin()
        ]);

    }

    #[Route('/account', name: '_account_page')]
    public function showProfile(Request $request): Response
    {
        $form = $this->createForm(UserType::class, $this->getUser());

        // remove roles and password
        $form->remove('roles');
        $form->remove('password');

        // Handle the form submission
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Save the updated user information
            $user = $form->getData();
        }

        return $this->render('dashboard/account.html.twig', [
            'user' => $this->getUser(),
            'form' => $form->createView(),
            'isAdmin' => $this->getUser()->isAdmin()
        ]);
    }
}