<?php

namespace AppBundle\Services;

use AppBundle\Entity\ProductCategory;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;

class ProductsService
{
    private $em;
    private $columnNames;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->columnNames = array_values(
            array_diff(
                $this->em->getClassMetadata('AppBundle:Product')->getFieldNames(), ['isActive']
            )
        );
    }

    public function categoryExists($categoryName)
    {
        $category = $this->em->getRepository("AppBundle:ProductCategory")->findOneBy(['name' => $categoryName]);
        if($category instanceof ProductCategory) {
            return true;
        }
        else {
            return false;
        }
    }

    public function getProducts(Request $request)
    {
        $result = [
            'errors' => null,
        ];
        $products = null;
        $params = $this->handleParams($request);
        $products = $this->getDQLResult($params);
        if ($products) {
            $result['headers'] = $this->columnNames;
            $result['data'] = $products;
            $result['errors'] = $params['errors'];
        }
        return $result;
    }

    public function getErrors($products)
    {
        if ($products && count($products) > 0) {
            return null;
        }
        else {
            return 'Products with given params not found';
        }
    }

    public function getDQLResult($params, $adminAccess = false)
    {
        $startID = ($params['page']-1)*$params['products_per_page']+1;
        $endID = $params['page']*$params['products_per_page'];
        $qb = $this->em->createQueryBuilder();
        $qb->select(array('p'))
            ->from('AppBundle:Product', 'p')
            ->where('p.id >= :start AND p.id <= :end')
            ->andWhere()
            ->setParameters([
                'start' => $startID,
                'end' => $endID
            ]);
        if(isset($params['sort_field'])) {
            $qb->orderBy('p.'.$params['sort_field'], $params['order_by']);
        }
        else {
            $qb->orderBy('p.id', 'ASC');
        }
        if(isset($params['filter_field'])) {
            $qb->where(
                $qb->expr()->like('p.'.$params['filter_field'], $params['filter_pattern'])
            );
        }
        $query = $qb->getQuery();
        return $query->getArrayResult();
    }

    public function handleParams(Request $request)
    {
        $page = $request->query->get('page');
        $category = $request->query->get('category');
        $sortField = $request->query->get('sort_field');
        $sortBy = $request->query->get('order_by');
        $filterField = $request->query->get('filter_field');
        $filterPattern = $request->query->get('filter_pattern');

        $params = [
            'products_per_page' => $request->request->get('products_per_page'),
            'errors' => []
        ];

        $params['page'] = $this->isValidPage($page) ? intval($page) : 1;

        if ($this->toBeSorted($sortField, $sortBy)) {
            $params['sort_field'] = $sortField;
            $params['order_by'] = strtoupper($sortBy);
        }

        if ($this->toBeFiltered($filterField, $filterPattern)) {
            $params['filter_field'] = $filterField;
            $params['filter_pattern'] = $filterPattern;
        }

        if ($this->isValidParam($category) && $this->categoryExists($category)) {
            $params['category'] = $category;
        }
        elseif(strlen($category)>0) {
            $params['errors'][] = 'Category '.$category.' does not exist';
        }

        if ($params['page'] === 0) {
            $params['page'] = 1;
            $params['errors'][] = 'Page format is incorrect';
        }

        if(intval($params['products_per_page']) == 0) {
            $params['products_per_page'] = 10;
        }

        return $params;
    }

    public function isValidParam($param)
    {
        return isset($param) && !empty($param);
    }

    public function isValidPage($page) {

        return filter_var($page, FILTER_VALIDATE_INT);
    }

    public function toBeSorted($field, $type)
    {
        $orders = ['ASC', 'DESC'];

        return  $this->isValidParam($field) &&
                $this->isValidParam($type) &&
                $type !== 'no_sort' &&
                in_array(strtoupper($type), $orders);
    }

    public function toBeFiltered($field, $pattern)
    {
        $allowedFields = ['name', 'description'];

        return  $this->isValidParam($field) &&
                $this->isValidParam($pattern) &&
                $field !== 'no_filter' &&
                in_array(strtolower($field), $allowedFields);
    }
}