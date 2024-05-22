<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class ErrorController extends AbstractController
{
    public function show(TranslatorInterface $translator): Response
    {
        $this->addFlash('error', $translator->trans('error.page.not_found'));
        return $this->redirectToRoute('app_home');
    }
}
