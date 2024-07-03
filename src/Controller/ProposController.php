<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProposController extends AbstractController
{
    #[Route('/propos', name: 'propos.index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('pages/propos/index.html.twig');
    }
}
