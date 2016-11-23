<?php

namespace AppBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route("/products")
 */
class ProductController extends Controller
{
    /**
     * @Route("/get")
     */
    public function getProducts(Request $request)
    {
        $productsService = $this->get('products_service');
        $products = $productsService->getProducts($request);

        return new JsonResponse($products);
    }

    /**
     * @Route("/get-template")
     */
    public function getTemplate(Request $request)
    {
        return $this->render('grid-template.html');
    }

    /**
     * @Route("/{category}/{page}")
     */
    public function showProducts($category, $page)
    {
        return $this->render('catalog.html.twig', [
            'user' => $this->getUser(),
            'errors' => null,
        ]);
    }

}
