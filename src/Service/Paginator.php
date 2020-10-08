<?php

namespace App\Service;

use Doctrine\ORM\EntityRepository;

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
        $this->totalItems = (int) $this->loadTotal();
        $this->setLimit($limit);
    }

    private function loadTotal()
    {
        return $this->entityRepository
            ->createQueryBuilder('e')
            ->select('count(e.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function getDataSet()
    {
        return $this->entityRepository->createQueryBuilder('e')
            ->orderBy('e.id', $this->order)
            ->setMaxResults($this->limit)
            ->setFirstResult($this->offset)
            ->getQuery()
            ->getResult();
    }

    public function getPage($page = 1, $meta = false)
    {
        if (!is_numeric($page)) {
            throw new \TypeError("The number of asked page must be an int!");
        }

        if ($page > $this->totalPages) {
            throw new \Exception("The page that you are looking for, does not exist!");
        }

        $this->currentPage = (int) $page;

        if ($page > 1) {
            $this->offset = (int) ceil($page * $this->limit);
        }

        $dataSet = $this->getDataSet();

        if ($meta) {
            return $this->addMeta($dataSet);
        }

        return $dataSet;
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

        $this->totalPages = (int) round($this->totalItems / $this->limit);

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
