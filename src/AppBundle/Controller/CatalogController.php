<?php

namespace AppBundle\Controller;

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
        /*if(!$user) {
            return new RedirectResponse('login');
        }*/
        return $this->render(
            'catalog.html.twig',
            ['user' => $user]
        );
    }
}
