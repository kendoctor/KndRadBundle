<?php

namespace Knd\Bundle\RadBundle\Manager;

use Symfony\Component\DependencyInjection\Container;

/**
 * Class ManagerFactory
 * @package Knd\Bundle\RadBundle\Manager
 */
class ManagerFactory
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param $class
     * @return Manager
     */
    public function create($class)
    {
        return new Manager($class, $this->container);
    }
}
