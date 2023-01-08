<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\DateSelectorType;
use App\Repository\UserRepository;
use Carbon\Carbon;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    #[Route('/admin/', name: 'app_admin_index')]
    public function index(
        UserRepository $userRepository,
        Request $request
    ): Response
    {
        $page = intval($request->get('page', '1'));
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

        $users = $userRepository->findUsersAndReportByDate(
            $dates,
            intval($request->get('page', 1))
        );

        $selectDateForm = $this->createForm(DateSelectorType::class, null);

        $params = $request->get('date_selector');
        // dd($year, $month);

        return $this->render('admin/index.html.twig', [
            'user' => $this->getUser(),
            'accounts' => $users,
            'selectDateForm' => $selectDateForm,
            'page' => $page,
            'dates' => [
                'month' => $selectedMonth,
                'year' => $selectedYear,
                'range' => $dates
            ]
        ]);
    }
}
