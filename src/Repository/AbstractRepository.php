<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

abstract class AbstractRepository extends ServiceEntityRepository
{
    protected function paginate($target, $limit = 5, $offset = 0)
    {
        if (0 == $limit || 0 == $offset) {
            throw new \LogicException('$limit & $offstet must be greater than 0.');
        }

        $qb = $this
            ->createQueryBuilder($target)
            ->select($target);

        $pager = new Pagerfanta(new QueryAdapter($qb));
        $currentPage = ceil(($offset + 1) / $limit);
        $pager->setCurrentPage((int) $currentPage);
        $pager->setMaxPerPage((int) $limit);

        return $pager;
    }
}
