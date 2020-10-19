<?php

namespace App\Service;

use Doctrine\ORM\EntityRepository;
use App\Exception\WrongPageException;

class Paginator
{
    private $entityRepository;
    private $totalItems;
    private $currentPage;
    private $totalPages;
    private $offset = 0;
    private $limit = 5;
    private $order = 'ASC';


    public function __construct(EntityRepository $entityRepository, Int $limit = 5)
    {
        $this->entityRepository = $entityRepository;
        $this->setLimit($limit);
    }

    private function getDataSet()
    {
        $this->totalItems = (int) $this->entityRepository
            ->createQueryBuilder('e')
            ->select('count(e.id)')
            ->getQuery()
            ->getSingleScalarResult();

        return $this->entityRepository->createQueryBuilder('e')
            ->orderBy('e.id', $this->order)
            ->setMaxResults($this->limit)
            ->setFirstResult($this->offset)
            ->getQuery()
            ->getResult();
    }

    private function getFilteredDataSet($filter)
    {
        $field = array_keys($filter)[0];
        $idTarget = $filter[$field];

        $this->totalItems = (int) $this->entityRepository
            ->createQueryBuilder('e')
            ->andWhere('e.' . $field . ' = :idClient')
            ->setParameter('idClient', $idTarget)
            ->select('count(e.id)')
            ->getQuery()
            ->getSingleScalarResult();

        return $this->entityRepository
            ->createQueryBuilder('e')
            ->andWhere('e.' . $field . ' = :idClient')
            ->setParameter('idClient', $idTarget)
            ->orderBy('e.id', $this->order)
            ->setMaxResults($this->limit)
            ->setFirstResult($this->offset)
            ->getQuery()
            ->getResult();
    }

    public function getPage(Int $page = 1, bool $meta = false, array $filter = [])
    {
        if (!is_numeric($page)) {
            throw new \TypeError("The number of asked page must be an int!");
        }

        $this->currentPage = (int) $page;

        if ($page > 1) {
            $this->offset = (int) ceil($page * $this->limit);
        }

        if ($page === 2) {
            $this->offset = $this->limit;
        }

        $dataSet = $this->selectDataSet($filter);

        $this->totalPages = (int) ceil($this->totalItems / $this->limit);

        if ($page > $this->totalPages) {
            throw new WrongPageException("The page that you are looking for, does not exist!");
        }

        if ($meta) {
            return $this->addMeta($dataSet);
        }

        return $dataSet;
    }

    private function selectDataSet($filter)
    {
        if (empty($filter)) {
            return $this->getDataSet();
        }

        return $this->getFilteredDataSet($filter);
    }

    private function addMeta($dataSet)
    {
        $meta = [
            "current_page" => $this->currentPage,
            "total_page" => $this->totalPages,
            "total_item_returned" => count($dataSet),
            "total_items" => $this->totalItems,
            "offset" => $this->offset,
            "limit" => $this->limit,
        ];

        $dataSet = [
            "data" => $dataSet,
            "meta" => $meta
        ];

        return $dataSet;
    }

    public function setLimit($limit)
    {
        if (!is_int($limit)) {
            throw new \TypeError("The limit must be an int!");
        }

        $this->limit = $limit;

        return $this;
    }

    public function setOrder($order)
    {
        if ($order !== 'DESC' || $order !== 'ASC') {
            throw new \Exception("Order value must be 'ASC' or 'DESC'!");
        }

        $this->order = $order;

        return $this;
    }
}
