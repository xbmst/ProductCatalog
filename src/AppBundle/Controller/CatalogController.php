<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class CatalogController extends Controller
{
    /**
     * @Route("/catalog", name="catalog")
     */
    public function showAction(Request $request)
    {
        $user = $this->getUser();

        return $this->render(
            'catalog.html.twig',
            ['user' => $user]
        );
    }
}
