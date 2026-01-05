<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TechnologyStackController extends AbstractController
{
    #[Route('/technology-stack', name: 'app_technology_stack')]
    public function index(): Response
    {
        return $this->render('technology_stack/index.html.twig');
    }
}
