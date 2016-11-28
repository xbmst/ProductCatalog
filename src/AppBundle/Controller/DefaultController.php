<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function defaultAction()
    {
        if ($this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute("products_all");
        }
        return $this->render(
        'default/index.html.twig'
        );
    }
}
