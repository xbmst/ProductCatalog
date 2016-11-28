<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Product;
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
        $response = $productsService->getData($request);
        $response['admin_access'] = $this->isGranted(['ROLE_MOD', 'ROLE_ADMIN']) ? true : false;
        return new JsonResponse($response);
    }

    /**
     * @Route("/get-pages-amount")
     */
    public function getPageAmount(Request $request)
    {
        $rowsPerPage = $request->query->get('rows_per_page');
        return new JsonResponse($this->get('products_service')->getPageAmount($rowsPerPage));
    }

    /**
     * @Route("/get-template")
     */
    public function getTemplate(Request $request)
    {
        return $this->render('grid-template.html');
    }

    /**
     * @Route("/", name="products_all")
     * @Route("/{category}")
     * @Route("/{category}/{page}")
     */
    public function showProducts($category, $page)
    {
        $categories = $this->get('category_service')->getFirstLevel();
        return $this->render('catalog.html.twig', [
            'user' => $this->getUser(),
            'errors' => null,
            'categories' => $categories,
        ]);
        /*if ($productsService->categoryExists($category)) {
            $products = $this->getDoctrine()->getRepository('AppBundle:Product')->findAll();
        } else {
            $message = 'Category';
        }

        return $this->render('admin/product/list.html.twig', [
            'products' => $products,
            'message' => $message,
        ]);*/
    }

}
