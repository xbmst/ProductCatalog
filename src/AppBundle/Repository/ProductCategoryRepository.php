<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class ProductCategoryRepository extends EntityRepository
{
    public function createAlphabeticalQueryBuilder()
    {
        return $this->createQueryBuilder('category')
            ->orderBy('category.name', 'ASC');
    }
}
