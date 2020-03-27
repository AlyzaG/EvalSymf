<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class NewController extends AbstractController
{
    /**
     * @Route("/", name="new")
     */
    public function index()
    {
        return $this->render('new/index.html.twig', [
        ]);
    }
}
