<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MentoringController extends AbstractController
{
    #[Route('/mentoring', name: 'app_mentoring')]
    public function index(): Response
    {
        return $this->render('mentoring/index.html.twig', [
            'controller_name' => 'MentoringController',
        ]);
    }
}
