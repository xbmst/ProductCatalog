<?php

namespace AppBundle\Repository;

use AppBundle\Entity\ProductCategory;
use Doctrine\ORM\EntityRepository;

class ProductCategoryRepository extends EntityRepository
{
    public function createAlphabeticalQueryBuilder()
    {
        return $this->createQueryBuilder('category')
            ->orderBy('category.name', 'ASC');
    }

    public function getCategories()
    {
        return $this->createQueryBuilder('category')
            ->where('category.parent is null')
            ->leftJoin('category.children', 'children', 'WITH', 'children.parent = category')
            ->orderBy('category.name', 'DESC');
    }

    public function getOrderedCategories()
    {
        $entity = $this->getCategories();
        $arr = array();

        foreach ($entity as $cat) {
            $arr[] = $cat;

            foreach ($cat->$this->getChildren() as $child) {
                $arr[] = $child;
            }
        }
    }

    public function createOrderedByParentQueryBuilder()
    {
        return $this->createQueryBuilder('category')
            ->orderBy('category.parent', 'ASC');
    }

    public function getCategoriesByParent(ProductCategory $parent = null)
    {
        $qb = $this->createQueryBuilder('c')
            ->orderBy('c.name', 'ASC');

        if (is_null($parent)) {
            $qb->andWhere('c.parent IS NULL');
        } else {
            $qb->andWhere('c.parent = :parent_id')
                ->setParameter('parent_id', $parent->getId());
        }

        return $qb->getQuery()->getResult();
    }

    public function getAllRootCategories()
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT c FROM AppBundle:ProductCategory c WHERE c.parent IS NULL ORDER BY c.name ASC'
            )
            ->getResult();
    }
}
