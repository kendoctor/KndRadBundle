<?php

namespace Knd\Bundle\RadBundle\Manager;

use Symfony\Component\DependencyInjection\Container;

class ManagerFactory
{
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function create($class)
    {
        return new Manager($class, $this->container);
    }
}
