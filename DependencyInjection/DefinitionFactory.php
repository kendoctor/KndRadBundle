<?php

namespace Knd\Bundle\RadBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Definition;

class DefinitionFactory
{
    public function createDefinition($classContainerParameter)
    {
        $definition = new Definition();
        $definition->setClass(sprintf('%%%s%%', $classContainerParameter));
        return $definition;
    }

    public function createDoctrineRepositoryDefinition($classContainerParameter)
    {
        $definition = new Definition();
        $definition->setClass('Doctrine\Common\Persistence\ObjectRepository');
        $definition->setFactoryService('doctrine');
        $definition->setFactoryMethod('getRepository');
        $definition->setArguments(array(sprintf('%%%s%%', $classContainerParameter)));

        return $definition;
    }

    public function createClassManagerDefinition($classContainerParameter)
    {
        $definition = new Definition();
        $definition->setClass('Knd\Bundle\Manager\Manager');
        $definition->setFactoryService('knd.factory.manager');
        $definition->setFactoryMethod('create');
        $definition->setArguments(array(sprintf('%%%s%%', $classContainerParameter)));

        return $definition;
    }
}
