<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ErrorController extends AbstractController
{
    public function show(): Response
    {
        $this->addFlash('error', 'Cette page n\'existe pas');
        return $this->redirectToRoute('app_home');
    }
}
