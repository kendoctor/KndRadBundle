<?php

namespace Knd\Bundle\RadBundle\Manager;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;

class Manager
{
    protected $class;

    protected $paginator;

    protected $entityManager;

    public function __construct($class,
                                EntityManagerInterface $entityManager,
                                PaginatorInterface $paginator = null)
    {
        $this->class = $class;
        $this->paginator = $paginator;
        $this->entityManager = $entityManager;
    }

    public function getRepository()
    {
        return $this->entityManager->getRepository($this->class);
    }

    public function getClass()
    {
        return $this->class;
    }

    public function setClass($class)
    {
        $this->class = $class;
    }

    public function create()
    {
        return new $this->class;
    }

    protected function createIndexQueryBuilder($criteria = array())
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb
            ->from($this->getClass(), 'o')
            ->select('o');

        return $qb;
    }

    public function createPagination($criteria, $page, $limit)
    {
        return $this->paginator->paginate($this->createIndexQueryBuilder($criteria), $page, $limit);
    }
}
