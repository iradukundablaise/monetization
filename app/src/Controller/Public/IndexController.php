<?php

namespace App\Controller\Public;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    #[Route('/public/index', name: 'app_public_index')]
    public function index(): Response
    {
        return $this->redirectToRoute('app_dashboard_index');
    }
}
