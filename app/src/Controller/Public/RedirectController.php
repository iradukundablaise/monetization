<?php

namespace App\Controller\Public;

use App\Repository\UserRepository;
use App\Service\Yegob_WP_Service;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RedirectController extends AbstractController
{
    #[Route('/r/{postId}/{userId}', name: 'app_public_redirect')]
    public function index(
        $postId,
        $userId,
        UserRepository $userRepository,
        Yegob_WP_Service $yegob_WP_Service
    ): Response
    {
        $post = $yegob_WP_Service->getPostFromWP($postId);
        $user = $userRepository->find($userId);

        if($post && $user){
            $link = $post['link'];
            $link .= "?utm_source=".$user->getUsername()."&utm_medium=affiliate";
            return $this->redirect($link);
        }
        return new Response("Link not found");
    }
}
