<?php

namespace Knd\Bundle\RadBundle\DependencyInjection;

use Knd\Bundle\RadBundle\Reflection\ReflectionFactory;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class DefinitionFactory
 * @package Knd\Bundle\RadBundle\DependencyInjection
 */
class DefinitionFactory
{
    /**
     * @var ReflectionFactory
     */
    private $reflectionFactory;

    /**
     * @param ReflectionFactory $reflectionFactory
     */
    public function __construct(ReflectionFactory $reflectionFactory = null)
    {
        $this->reflectionFactory = $reflectionFactory ?: new ReflectionFactory();
    }

    /**
     * @param $serviceId
     * @return Reference
     */
    public function  createReference($serviceId)
    {
        return new Reference($serviceId);
    }

    /**
     * @param $class
     * @param $classContainerParameter
     * @return Definition
     * @throws \Exception
     */
    public function createDefinition($class, $classContainerParameter)
    {
        $definition = new Definition();
        $definition->setClass(sprintf('%%%s%%', $classContainerParameter));

        $refl = new \ReflectionClass($class);

        $parameters = null;

        if(method_exists($class, '__construct'))
        {
            $r = new \ReflectionMethod($class, '__construct');
            $parameters = $r->getParameters();
            if(count($parameters) > 0)
            {

                if (!$refl->implementsInterface('Knd\Bundle\RadBundle\DependencyInjection\AutoInjectInterface')) {
                    throw new \Exception(sprintf('%s : Auto inject for Non-constructor, Zero-parameter-constructor or implement Knd\Bundle\RadBundle\DependencyInjection\AutoInjectInterface class', $class));
                }
            }
        }

        $arguments = array();

        if($parameters)
        {
            $configs = call_user_func(array($class, 'getConstructorParameters'));

            if (count($parameters) !== count($configs)) {
                throw new \Exception(sprintf('%s : class constructor parameters count different with config for auto inject', $class));
            }

            foreach ($configs as $config) {
//                if(preg_match('/^%.+%$/', $parameterConfig, $matches))
//                {
//
//                }

                if(preg_match('/^@(.+)$/', $config, $matches))
                {
                    $arguments [] = $this->createReference($matches[1]);
                    continue;
                }

                $arguments[] = $config;
            }
        }

        $definition->setArguments($arguments);

        return $definition;
    }

    /**
     * @param $classContainerParameter
     * @param $repoClassContainerParameter
     * @return Definition
     */
    public function createEntityRepositoryDefinition($classContainerParameter, $repoClassContainerParameter)
    {
        $definition = new Definition();
        $definition->setClass(sprintf('%%%s%%', $repoClassContainerParameter));
        $definition->setArguments(array(
            $this->createReference('doctrine.orm.entity_manager'),
            $this->getClassMetadataDefinition($classContainerParameter)
        ));

        return $definition;
    }

    /**
     * @param $classContainerParameter
     * @return Definition
     */
    public function createEntityRepositoryByFactoryDefinition($classContainerParameter)
    {
        $definition = new Definition('Knd\Bundle\RadBundle\Repository\EntityRepository');

        $definition->setArguments(array(
            $this->createReference('doctrine.orm.entity_manager'),
            $this->getClassMetadataDefinition($classContainerParameter)
        ));

        return $definition;
    }

    /**
     * @param $classContainerParameter
     * @return Definition
     */
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

    /**
     * @param $classContainerParameter
     * @param $managerClassContainerParameter
     * @return Definition
     */
    public function createManagerDefinition($classContainerParameter, $managerClassContainerParameter)
    {
        $definition = new Definition();
        $definition->setClass(sprintf('%%%s%%', $managerClassContainerParameter));
        $definition->setArguments(array(
            sprintf('%%%s%%', $classContainerParameter),
            $this->createReference('service_container')
        ));

        return $definition;
    }

    /**
     * @param $classContainerParameter
     * @return Definition
     */
    public function createManagerByFactoryDefinition($classContainerParameter)
    {
        $definition = new Definition();
        $definition->setClass('Knd\Bundle\RadBundle\Manager\Manager');
        $definition->setFactoryService('knd_rad.manager.factory');
        $definition->setFactoryMethod('create');
        $definition->setArguments(array(sprintf('%%%s%%', $classContainerParameter)));

        return $definition;
    }


    /**
     * @param $class
     * @param $classContainerParameter
     * @return Definition
     * @throws \Exception
     */
    public function createFormTypeDefinition($class, $classContainerParameter)
    {
        $definition = $this->createDefinition($class, $classContainerParameter);
        $definition->addTag('form.type');
        $definition->setPublic(true);

        return $definition;
    }

    public function createModelVoterByFactoryDefinition($classContainerParameter)
    {
        $definition = new Definition();
        $definition->setClass('Knd\Bundle\RadBundle\Security\Voter\Voter');
        $definition->setPublic(false);
        $definition->addTag('security.voter');
        $definition->addTag('knd_rad.security.voter');
        $definition->setFactoryService('knd_rad.security.voter.factory');
        $definition->setFactoryMethod('create');
        $definition->setArguments(array(sprintf('%%%s%%', $classContainerParameter)));

        return $definition;
    }

    public function createModelVoterDefinition($class, $classContainerParameter)
    {
        $definition = $this->createDefinition($class, $classContainerParameter);
        $definition->setPublic(false);
        $definition->addTag('security.voter');
        $definition->addTag('knd_rad.security.voter');


        return $definition;
    }
}
