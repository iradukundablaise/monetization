<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\User1Type;
use App\Form\UserType;
use App\Repository\ReportRepository;
use App\Repository\UserRepository;
use Carbon\Carbon;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/user')]
class UserController extends AbstractController
{
    #[Route('/', name: 'app_admin_user_index', methods: ['GET'])]
    public function index(
        Request $request,
        UserRepository $userRepository
    ): Response
    {
        return $this->render('admin/user/index.html.twig', [
            'users' => $userRepository->findUsers($request->get('page', 1)),
            'user' => $this->getUser()
        ]);
    }

    #[Route('/new', name: 'app_admin_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, UserRepository $userRepository): Response
    {
        $user = new User();
        $form = $this->createForm(User1Type::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository->save($user, true);

            return $this->redirectToRoute('app_admin_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('admin/user/show.html.twig', [
            'user' => $this->getUser(),
            'account' => $user
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, UserRepository $userRepository): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository->save($user, true);

            return $this->redirectToRoute('app_admin_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/user/edit.html.twig', [
            'user' => $this->getUser(),
            'account' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, UserRepository $userRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $userRepository->remove($user, true);
        }

        return $this->redirectToRoute('app_admin_user_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/stats', name: 'app_admin_user_stats', methods: ['GET'])]
    public function stats(Request $request, User $user, ReportRepository $reportRepository): Response
    {
        $selectedYear = $request->get('year', date('Y'));
        $selectedMonth = $request->get('month', date('m'));

        $dates = [
            'startDate' => Carbon::createFromDate(
                intval($selectedYear),
                intval($selectedMonth)
            )->startOfMonth()->format('Y-m-d'),
            'endDate' => Carbon::createFromDate(
                intval($selectedYear),
                intval($selectedMonth)
            )->endOfMonth()->format('Y-m-d')
        ];

        $reports = $reportRepository->findReportsMonthly(
            $user->getId(),
            $selectedMonth,
            $selectedYear
        );

        return $this->render('admin/user/stats.html.twig', [
            'reports' => $reports,
            'user' => $this->getUser(),
            'dates' => [
                'month' => $selectedMonth,
                'year' => $selectedYear,
                'range' => $dates
            ]
        ]);
    }
}
