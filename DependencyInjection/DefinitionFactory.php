<?php

namespace Knd\Bundle\RadBundle\DependencyInjection;

use Knd\Bundle\RadBundle\Reflection\ReflectionFactory;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class DefinitionFactory
{
    private $reflectionFactory;

    public function __construct(ReflectionFactory $reflectionFactory = null)
    {
        $this->reflectionFactory = $reflectionFactory ?: new ReflectionFactory();
    }

    public function  createReference($serviceId)
    {
        return new Reference($serviceId);
    }

    public function createDefinition($class, $classContainerParameter)
    {
        $definition = new Definition();
        $definition->setClass(sprintf('%%%s%%', $classContainerParameter));

        $params = $this->reflectionFactory->getConstructorParameters($class);

        $arguments = array();
        foreach ($params as $param) {
            if(strpos($param->getName(), 'p_') === 0)
            {
                $tmpName  = str_replace('__', '.', substr($param->getName(), 2));
                $arguments[] = sprintf('%%%s%%', $tmpName);
            }
            elseif(strpos($param->getName(), 's_') === 0)
            {
                $serviceId  = str_replace('__', '.', substr($param->getName(), 2));
                $serviceRef = $this->createReference($serviceId);
                $arguments[] = $serviceRef;
            }
            else
            {
                throw new \Exception('Invalid DI name convention.');
            }
        }

        $definition->setArguments($arguments);

        return $definition;
    }

    public function createDoctrineRepositoryDefinition($classContainerParameter)
    {
        $definition = new Definition('Knd\Bundle\RadBundle\Repository\EntityRepository');

        $definition->setArguments(array(
            $this->createReference('doctrine.orm.entity_manager'),
            $this->getClassMetadataDefinition($classContainerParameter)
        ));

//        $definition = new Definition();
//        $definition->setClass('Doctrine\Common\Persistence\ObjectRepository');
//        $definition->setFactoryService('doctrine');
//        $definition->setFactoryMethod('getRepository');
//        $definition->setArguments(array(sprintf('%%%s%%', $classContainerParameter)));

        return $definition;
    }

    protected function getClassMetadataDefinition($classContainerParameter)
    {
        $definition = new Definition('Doctrine\ORM\Mapping\ClassMetadata');
        $definition
            ->setFactoryService('doctrine.orm.entity_manager')
            ->setFactoryMethod('getClassMetadata')
            ->setArguments(array(sprintf('%%%s%%', $classContainerParameter)))
            ->setPublic(false)
        ;

        return $definition;
    }

    public function createClassManagerDefinition($classContainerParameter)
    {
        $definition = new Definition();
        $definition->setClass('Knd\Bundle\RadBundle\Manager\Manager');
        $definition->setFactoryService('knd_rad.manager.factory');
        $definition->setFactoryMethod('create');
        $definition->setArguments(array(sprintf('%%%s%%', $classContainerParameter)));

        return $definition;
    }



    public function createFormTypeDefinition($class, $classContainerParameter)
    {
        $definition = $this->createDefinition($class, $classContainerParameter);
        $definition->addTag('form.type');
        $definition->setPublic(true);

        return $definition;
    }
}
