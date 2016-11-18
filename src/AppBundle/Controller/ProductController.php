<?php

namespace AppBundle\Controller;

use AppBundle\Form\ProductType;
use AppBundle\Entity\ProductCategory;
use AppBundle\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class ProductController extends Controller
{
    /**
     * @Route("/categories/{id}/create", name="create_product")
     */
    public function createAction(ProductCategory $category, Request $request)
    {
    }

    /**
     * @Route("/show-form", name="show_form")
     */
    public function showFormAction(Request $request)
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        return $this->render('product.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
