<?php

namespace Knd\Bundle\RadBundle\DependencyInjection\Compiler;

use Knd\Bundle\RadBundle\DependencyInjection\ContainerIdGenerator;
use Knd\Bundle\RadBundle\DependencyInjection\DefinitionFactory;
use Knd\Bundle\RadBundle\DependencyInjection\ServiceIdGenerator;
use Knd\Bundle\RadBundle\Finder\ClassFinder;
use Knd\Bundle\RadBundle\Reflection\ReflectionFactory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

/**
 * Class RegisterAutoInjectServicePass
 * @package Knd\Bundle\RadBundle\DependencyInjection\Compiler
 */
class RegisterAutoInjectServicePass implements CompilerPassInterface
{
    /**
     * @var BundleInterface
     */
    private $bundle;
    /**
     * @var ClassFinder
     */
    private $classFinder;
    /**
     * @var ServiceIdGenerator
     */
    private $serviceIdGenerator;
    /**
     * @var ReflectionFactory
     */
    private $reflectionFactory;
    /**
     * @var DefinitionFactory
     */
    private $definitionFactory;
    /**
     * @var ContainerIdGenerator
     */
    private $containerIdGenerator;

    /**
     * @param BundleInterface $bundle
     * @param ClassFinder $classFinder
     * @param null $definitionFactory
     * @param ServiceIdGenerator $serviceIdGenerator
     */
    public function __construct(BundleInterface $bundle, ClassFinder $classFinder = null,  $definitionFactory = null, ServiceIdGenerator $serviceIdGenerator = null)
    {
        $this->bundle             = $bundle;
        $this->classFinder        = $classFinder ?: new ClassFinder;
        $this->serviceIdGenerator = $serviceIdGenerator ?: new ServiceIdGenerator();
        $this->reflectionFactory  = new ReflectionFactory();
        $this->definitionFactory = $definitionFactory ?: new DefinitionFactory();
        $this->containerIdGenerator = new ContainerIdGenerator();
    }


    /**
     * @param $dirs
     * @param array $classes
     * @param array $excludeClasses
     * @param string $pattern
     * @return array
     */
    protected function getClasses($dirs, $classes = array(), $excludeClasses = array(), $pattern = '*.php')
    {
        $basePath = $this->bundle->getPath();

        $dirs = array_map(function ($dir) use ($basePath) {
            return $basePath.'/'.$dir;
        }, $dirs);

        $allClasses = $this->classFinder->findClasses(
            $dirs,
            $basePath,
            $this->bundle->getNamespace(),
            $pattern
        );

        $allClasses = array_unique(array_merge($classes, $allClasses));

       return array_filter($allClasses, function($class) use ($excludeClasses) {
            return !in_array($class, $excludeClasses);
        });

    }

    /**
     * @param ContainerBuilder $container
     * @param $class
     * @return string
     */
    protected function injectClassContainerParameter(ContainerBuilder $container, $class)
    {
        $classContainerParameter = $this->containerIdGenerator->getContainerParameter($class);

        if (!$container->hasParameter($classContainerParameter)) {
            $container->setParameter($classContainerParameter, $class);
        }

        return $classContainerParameter;
    }

    /**
     * @param ContainerBuilder $container
     */
    protected function injectServicesRelatedEntity(ContainerBuilder $container)
    {
        $config = $container->getParameter('knd_rad.auto_inject.entity');

        $pattern = sprintf('/(?<!%s)\.php$/', implode('|', $config['ignore_suffix']));

        foreach($this->getClasses($config['dirs'], $config['classes'], $config['exclude_classes'], $pattern) as $class)
        {
            $reflClass = $this->reflectionFactory->createReflectionClass($class);

            if($reflClass->isAbstract())
            {
                continue;
            }

            $classContainerParameter = $this->injectClassContainerParameter($container, $class);

            $this->injectDoctrineRepositoryService($config['repository'], $container, $class, $classContainerParameter);

            $this->injectClassManagerService($config['manager'], $container, $class, $classContainerParameter);

            $this->injectModelVoterService($config['voter'], $container, $class, $classContainerParameter);
        }
    }

    protected function injectModelVoterService($config, ContainerBuilder $container, $class, $classContainerParameter)
    {

        if (false === $container->hasDefinition('security.access.decision_manager')) {
            return;
        }

        $decisionManagerDef = $container->getDefinition('security.access.decision_manager');
        $voterStack = $container->getDefinition('knd_rad.security.voter.stack');

        $modelVoterClass =  $this->containerIdGenerator->guessModelVoterClass($class, $config['dir']);
        $id = $this->containerIdGenerator->getModelVoterServiceId($modelVoterClass);

        if ($container->hasDefinition($id)) {
            return;
        }

        if(!class_exists($modelVoterClass) && $config['auto'])
        {
            $def = $this->definitionFactory->createModelVoterByFactoryDefinition($classContainerParameter);

            $container->setDefinition($id, $def);

            $values = $decisionManagerDef->getArgument(0);
            $values[] = $def;
            $decisionManagerDef->replaceArgument(0, $values);

            $values = $voterStack->getArgument(0);
            $values[] = $def;
            $voterStack->replaceArgument(0, $values);



        }
        elseif(class_exists($modelVoterClass))
        {
            $reflClass = $this->reflectionFactory->createReflectionClass($modelVoterClass);

            if($reflClass->isAbstract() || !$reflClass->isSubclassOf('Knd\Bundle\RadBundle\Security\Voter\AbstractVoter'))
            {
                return;
            }

            $classContainerParameter = $this->containerIdGenerator->getContainerParameter($class);
            $voterClassContainerParameter = $this->injectClassContainerParameter($container, $modelVoterClass);

            $def = $this->definitionFactory->createModelVoterDefinition($modelVoterClass, $voterClassContainerParameter);

            $container->setDefinition($id, $def);

            $values = $decisionManagerDef->getArgument(0);
            $values[] = $def;
            $decisionManagerDef->replaceArgument(0, $values);

            $values = $voterStack->getArgument(0);
            $values[] = $def;
            $voterStack->replaceArgument(0, $values);

        }

    }
        /**
     * @param $config
     * @param ContainerBuilder $container
     * @param $class
     * @param $classContainerParameter
     */
    protected function injectDoctrineRepositoryService($config, ContainerBuilder $container, $class, $classContainerParameter)
    {
        $id = $this->containerIdGenerator->getEntityRepositoryServiceId($class);

        if ($container->hasDefinition($id)) {
            return;
        }

        $repositoryClass =  $this->containerIdGenerator->guessEntityRepositoryClass($class, $config['dir']);
        if(!class_exists($repositoryClass) && $config['auto'])
        {
            $def = $this->definitionFactory->createEntityRepositoryByFactoryDefinition($classContainerParameter);

            $container->setDefinition($id, $def);
        }
        elseif(class_exists($repositoryClass))
        {
            $reflClass = $this->reflectionFactory->createReflectionClass($repositoryClass);

            if($reflClass->isAbstract() || !$reflClass->isSubclassOf('Doctrine\ORM\EntityRepository'))
            {
                return;
            }

            $classContainerParameter = $this->containerIdGenerator->getContainerParameter($class);
            $repoClassContainerParameter = $this->injectClassContainerParameter($container, $repositoryClass);

            $def = $this->definitionFactory->createEntityRepositoryDefinition($classContainerParameter, $repoClassContainerParameter);

            $container->setDefinition($id, $def);
        }


    }

    /**
     * @param $config
     * @param ContainerBuilder $container
     * @param $class
     * @param $classContainerParameter
     */
    protected function injectClassManagerService($config, ContainerBuilder $container, $class, $classContainerParameter)
    {

        $id = $this->containerIdGenerator->getManagerServiceId($class);

        if ($container->hasDefinition($id)) {
            return;
        }

        $managerClass = $this->containerIdGenerator->guessManagerClass($class, $config['dir']);

        if(!class_exists($managerClass) && $config['auto'])
        {
            $def = $this->definitionFactory->createManagerByFactoryDefinition($classContainerParameter);

            $container->setDefinition($id, $def);
        }
        elseif(class_exists($managerClass))
        {
            $reflClass = $this->reflectionFactory->createReflectionClass($managerClass);

            if($reflClass->isAbstract() || !$reflClass->isSubclassOf('Knd\Bundle\RadBundle\Manager\Manager'))
            {
                return;
            }

            $classContainerParameter = $this->containerIdGenerator->getContainerParameter($class);
            $managerClassContainerParameter = $this->injectClassContainerParameter($container, $managerClass);

            $def = $this->definitionFactory->createManagerDefinition($classContainerParameter, $managerClassContainerParameter);

            $container->setDefinition($id, $def);

        }


    }

    /**
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    protected function injectCommonServices(ContainerBuilder $container)
    {

        $config = $container->getParameter('knd_rad.auto_inject.common');

        $types = null;
        $def = null;

        if($container->hasDefinition('form.extension'))
        {
            $types   = $container->getDefinition('form.extension')->getArgument(1);
        }

        foreach($this->getClasses($config['dirs'], $config['classes'], $config['exclude_classes']) as $class)
        {
            $reflClass = $this->reflectionFactory->createReflectionClass($class);

            if($reflClass->isAbstract())
            {
                continue;
            }

            $classContainerParameter = $this->injectClassContainerParameter($container, $class);

            if ($reflClass->implementsInterface('Symfony\Component\Form\FormTypeInterface')) {

                $id = $this->containerIdGenerator->getFormTypeServiceId($class);

                if ($container->hasDefinition($id)) {
                    continue;
                }

                if (false === $container->hasDefinition('form.extension')) {
                    continue;
                }

                $def = $this->definitionFactory->createFormTypeDefinition($class, $classContainerParameter);


                $alias = $this->containerIdGenerator->getFormTypeAlias($class);

                $types[$alias] = $id;

            }elseif($reflClass->isSubclassOf('Doctrine\ORM\EntityRepository'))
            {
                continue;

            } elseif($reflClass->isSubclassOf('Knd\Bundle\RadBundle\Manager\Manager')) {
                continue;
            } elseif($reflClass->isSubclassOf('Knd\Bundle\RadBundle\Security\Voter\AbstractVoter'))
            {
                $id = $this->containerIdGenerator->getModelVoterServiceId($class);
                $def = $this->definitionFactory->createModelVoterDefinition($class, $classContainerParameter);
            }
            else
            {
                $id = $this->containerIdGenerator->getServiceId($class);

                if ($container->hasDefinition($id)) {
                    continue;
                }

                $def = $this->definitionFactory->createDefinition($class, $classContainerParameter);
            }

            if($def)
            {
                $container->setDefinition($id, $def);
            }
        }

        if($container->hasDefinition('form.extension'))
        {
            $container->getDefinition('form.extension')->replaceArgument(1, $types);
        }

    }

    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $this->injectCommonServices($container);

        $this->injectServicesRelatedEntity($container);


    }
}
