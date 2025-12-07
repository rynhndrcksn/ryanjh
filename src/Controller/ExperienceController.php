<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\JobRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ExperienceController extends AbstractController
{
    #[Route('/experience', name: 'app_experience')]
    public function index(JobRepository $jobRepository): Response
    {
        return $this->render('experience/index.html.twig', [
            'jobs' => $jobRepository->findAllOrderedByEndDate(),
        ]);
    }
}
