<?php
namespace AppBundle\Services;


use Doctrine\ORM\EntityManager;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;

class DataProcessor
{
    private $em;
    private $repository = null;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function getStartID(array $params)
    {
        return ($params['page']-1)*$params['rows_per_page'];
    }

    public function getBasicQueryParams(array $params)
    {

        return [
            'start' => $this->getStartID($params),
        ];
    }

    public function getBasicDQL(Request $request)
    {
        $params = $this->handleParams($request);
        $qb = $this->em->createQueryBuilder();
        $qb->select(array('d'))
            ->from('AppBundle:'.$this->repository, 'd');
        if(!$this->needsComplexProcessing($params)) {
            $start = $this->getStartID($params);
            $qb->setFirstResult($start)
                ->setMaxResults($params['rows_per_page']);
        }
        else {

        }

        return $qb;
    }

    public function needsComplexProcessing($params)
    {
        return  $this->toBeFiltered($params['filter_field'], $params['filter_pattern']) ||
                $this->toBeSorted($params['sort_field'], $params['order_by']);
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
        $allowedFields = ['name', 'description', 'is_active'];

        return  $this->isValidParam($field) &&
        $this->isValidParam($pattern) &&
        $field !== 'none' &&
        in_array(strtolower($field), $allowedFields);
    }

    public function getLastID($start, $field, $pattern, $max)
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
        $page = intval($request->query->get('page'));
        $category = $request->query->get('category');
        $sortField = $request->query->get('sort_field');
        $orderBy = $request->query->get('order_by');
        $filterField = $request->query->get('filter_field');
        $filterPattern = $request->query->get('filter_pattern');
        $filterLastId = $request->query->get('filter_last_id');
        $rowsPerPage = $request->query->get('rows_per_page');

        $params = [
            'page' => 1,
            'sort_field' => null,
            'order_by' => null,
            'filter_field' => null,
            'filter_pattern' => null,
            'category' => null,
            'rows_per_page' => $rowsPerPage,
            'filter_last_id' => null,
            'errors' => [],
        ];

        if ($this->isValidParam($filterLastId) && intval($filterLastId) > 0) {
            $params['filter_last_id'] = intval($filterLastId);
        }

        $this->setFilterParams($params, $filterField, $filterPattern);
        $this->setSortingParams($params, $sortField, $orderBy);
        $this->setPage($params, $page);
        $this->setMax($params, $rowsPerPage);

        return $params;
    }

    public function setRepository($repository)
    {
        $allowed = ['User', 'Product'];
        if(in_array($repository, $allowed)) {
            $this->repository = $repository;
        }
        else {
            throw new Exception('No repository '.$repository.' found');
        }
    }

    public function setMax(&$result, $max)
    {
        $max = intval($max);
        $result['rows_per_page'] = ($max == 0) ? 10 : $max;
    }

    public function setFilterParams(&$result, $field, $pattern)
    {
        if ($this->toBeFiltered($field, $pattern)) {
            $result['filter_field'] = $field;
            $result['filter_pattern'] = $pattern;
        }
        else {
            $result['filter_field'] = $result['filter_pattern'] = null;
        }
    }

    public function setSortingParams(&$result, $field, $type)
    {
        if($this->toBeSorted($field, $type)) {
            $result['sort_field'] = $field;
            $result['order_by'] = strtoupper($type);
        }
        else {
            $result['sort_field'] = $result['order_by'] = null;
        }
    }

    public function setPage(&$result, $page)
    {
        $result['page'] = $this->isValidPage($page) ? $page : 1;
    }

    public function isValidParam($param)
    {
        return !is_null($param) && isset($param) && !empty($param);
    }

    public function isValidPage($page) {

        return filter_var($page, FILTER_VALIDATE_INT);
    }

}

/*$startID = $this->getStartID($params);

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
}*/

/*if (isset($params['filter_field'])) {
    $qb->andWhere(
        $qb->expr()->like('p.'.$params['filter_field'], ':filter')
    );
    $queryParameters['filter'] = '%'.$params['filter_pattern'].'%';
    $params['filter_last_id'] = $this->getFilterLastID(
        $startID, $params['filter_field'], $queryParameters['filter'], $params['rows_per_page']
    );
}*/
/*$qb->setParameters($queryParameters);
$query = $qb->getQuery();
$result = [
    'products' => $query->getArrayResult(),
    'filter_last_id' => null,
];
if ($params['filter_last_id']) {
    $result['filter_last_id'] = $params['filter_last_id'];
}


return $result;*/