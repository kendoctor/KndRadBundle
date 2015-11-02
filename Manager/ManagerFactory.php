<?php

namespace Knd\Bundle\RadBundle\Manager;

class ManagerFactory
{
    protected $knpPaginator;
    protected $entityManager;

    public function __construct($entityManager, $knpPaginator = null)
    {
        $this->entityManager = $entityManager;
        $this->knpPaginator = $knpPaginator;
    }

    public function create($class)
    {
        return new Manager($class, $this->entityManager, $this->knpPaginator);
    }
}
