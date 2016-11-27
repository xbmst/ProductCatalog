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
        $dqlResult = $this->getDQLResult($params);
        $products = $dqlResult['products'];

        $result['headers'] = $this->columnNames;
        if ($this->isValidParam($dqlResult['filter_last_id'])) {
            $result['filter_last_id'] = $dqlResult['filter_last_id'];
        }
        $result['data'] = $products;
        $result['errors'] = $params['errors'];

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

    public function getPageAmount($rowsPerPage)
    {
        $rowsPerPage = intval($rowsPerPage);
        $rowsPerPage = $this->isValidPage($rowsPerPage) ? $rowsPerPage : 10;
        $pages =  $this->em
            ->createQuery('SELECT COUNT(p) FROM AppBundle:Product p')
            ->getSingleScalarResult();
        $pages = intval($pages);
        return ceil($pages/$rowsPerPage);
    }

    public function getDQLResult($params, $adminAccess = false)
    {
        $startID = ($params['page']-1)*$params['rows_per_page'];

        if($params['filter_last_id']) {
            $lastID = $params['filter_last_id'];
            if($this->isValidParam($lastID) && $this->isValidPage($lastID)) {
                $startID = $lastID;
            }
        }
        $queryParameters = [
            'start' => $startID,
        ];
        $qb = $this->em->createQueryBuilder();
        $qb->select(array('p'))
            ->from('AppBundle:Product', 'p')
            ->where('p.id > :start')
            ->setMaxResults($params['rows_per_page']);
        if(isset($params['sort_field'])) {
            $qb->orderBy('p.'.$params['sort_field'], $params['order_by']);
        }
        else {
            $qb->orderBy('p.id', 'ASC');
        }
        /*if(isset($params['category'])) {
            $qb->andWhere();
        }*/
        if (isset($params['filter_field'])) {
            $qb->andWhere(
                $qb->expr()->like('p.'.$params['filter_field'], ':filter')
            );
            $queryParameters['filter'] = '%'.$params['filter_pattern'].'%';
            $params['filter_last_id'] = $this->getFilterLastID(
                $startID, $params['filter_field'], $queryParameters['filter'], $params['rows_per_page']
            );
        }
        $qb->setParameters($queryParameters);
        $query = $qb->getQuery();
        $result = [
            'products' => $query->getArrayResult(),
            'filter_last_id' => null,
        ];
        if ($params['filter_last_id']) {
            $result['filter_last_id'] = $params['filter_last_id'];
        }


        return $result;
    }

    public function getFilterLastID($start, $field, $pattern, $max)
    {
        $last = $this->em->createQuery(
            'SELECT p.id FROM AppBundle:Product p WHERE p.id > :startID AND p.'.$field.' LIKE :pattern'
        )
            ->setMaxResults($max)
            ->setParameters([
                'startID' => $start,
                'pattern' => $pattern,
            ])
            ->getArrayResult();
        return intval(array_pop($last)['id']);
    }

    public function getSortingLastID()
    {

    }

    public function handleParams(Request $request)
    {
        $page = $request->query->get('page');
        $category = $request->query->get('category');
        $sortField = $request->query->get('sort_field');
        $sortBy = $request->query->get('order_by');
        $filterField = $request->query->get('filter_field');
        $filterPattern = $request->query->get('filter_pattern');
        $filterLastId = $request->query->get('filter_last_id');

        $params = [
            'rows_per_page' => $request->query->get('rows_per_page'),
            'filter_last_id' => null,
            'errors' => [],
        ];

        $params['page'] = $this->isValidPage($page) ? intval($page) : 1;

        if ($this->isValidParam($filterLastId) && intval($filterLastId) > 0) {
            $params['filter_last_id'] = intval($filterLastId);
        }

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

        if(intval($params['rows_per_page']) == 0) {
            $params['rows_per_page'] = 10;
        }

        return $params;
    }

    public function isValidParam($param)
    {
        return !is_null($param) && isset($param) && !empty($param);
    }

    public function isValidPage($page) {

        return filter_var($page, FILTER_VALIDATE_INT);
    }

    public function toBeSorted($field, $type)
    {
        $orders = ['ASC', 'DESC'];

        return  $this->isValidParam($field) &&
                $this->isValidParam($type) &&
                $field !== 'none' &&
                in_array(strtoupper($type), $orders);
    }

    public function toBeFiltered($field, $pattern)
    {
        $allowedFields = ['name', 'description'];

        return  $this->isValidParam($field) &&
                $this->isValidParam($pattern) &&
                $field !== 'none' &&
                in_array(strtolower($field), $allowedFields);
    }
}
