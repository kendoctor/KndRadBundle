<?php

namespace Knd\Bundle\RadBundle\DependencyInjection\Compiler;

use Knd\Bundle\RadBundle\DependencyInjection\DefinitionFactory;
use Knd\Bundle\RadBundle\DependencyInjection\ServiceIdGenerator;
use Knd\Bundle\RadBundle\Finder\ClassFinder;
use Knd\Bundle\RadBundle\Reflection\ReflectionFactory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class RegisterAutoInjectServicePass implements CompilerPassInterface
{
    private $bundle;
    private $classFinder;
    private $serviceIdGenerator;
    private $reflectionFactory;
    private $definitionFactory;

    public function __construct(BundleInterface $bundle, ClassFinder $classFinder = null,  $definitionFactory = null, ServiceIdGenerator $serviceIdGenerator = null)
    {
        $this->bundle             = $bundle;
        $this->classFinder        = $classFinder ?: new ClassFinder;
        $this->serviceIdGenerator = $serviceIdGenerator ?: new ServiceIdGenerator();
        $this->reflectionFactory  = new ReflectionFactory();
        $this->definitionFactory = $definitionFactory ?: new DefinitionFactory();
    }


    protected function getClasses($dirs, $ignoreSuffix = array())
    {
        $classes = array();
        foreach ($dirs as $dir) {
            $directory = sprintf("%s/%s", $this->bundle->getPath(), $dir);
            $namespace = sprintf("%s\\%s", $this->bundle->getNamespace(), $dir);
            $classes = array_merge($classes, $this->classFinder->findClasses($directory, $namespace, $ignoreSuffix));
        }

        return $classes;
    }

    protected function injectClassContainerParameter(ContainerBuilder $container, $class)
    {
        $classContainerParameter = $this->serviceIdGenerator->generateClassContainerParameter($this->bundle, $class);

        if (!$container->hasParameter($classContainerParameter)) {
            $container->setParameter($classContainerParameter, $class);
        }

        return $classContainerParameter;
    }

    protected function injectServicesRelatedEntity(ContainerBuilder $container)
    {
        $config = $container->getParameter('knd_rad.auto_inject.entity');

        foreach($this->getClasses($config['dirs'], $config['ignore_suffix']) as $class)
        {
            $reflClass = $this->reflectionFactory->createReflectionClass($class);

            if($reflClass->isAbstract())
            {
                continue;
            }

            $classContainerParameter = $this->injectClassContainerParameter($container, $class);

            if($config['repository'])
            {
                $this->injectDoctrineRepositoryService($container, $class, $classContainerParameter);
            }

            if($config['manager'])
            {
                $this->injectClassManagerService($container, $class, $classContainerParameter);
            }
        }
    }

    protected function injectDoctrineRepositoryService(ContainerBuilder $container, $class, $classContainerParameter)
    {
        if (!strpos($class, $this->bundle->getNamespace()) === 0) {
            return;
        }

        $id = $this->serviceIdGenerator->generateClassRepositoryId($this->bundle, $class);

        if ($container->hasDefinition($id)) {
            return;
        }

        $def = $this->definitionFactory->createDoctrineRepositoryDefinition($classContainerParameter);

        $container->setDefinition($id, $def);
    }

    protected function injectClassManagerService(ContainerBuilder $container, $class, $classContainerParameter)
    {
        if (!strpos($class, $this->bundle->getNamespace()) === 0) {
            return;
        }

        $id = $this->serviceIdGenerator->generateClassManagerId($this->bundle, $class);

        if ($container->hasDefinition($id)) {
            return;
        }

        $def = $this->definitionFactory->createClassManagerDefinition($classContainerParameter);

        $container->setDefinition($id, $def);
    }

    protected function injectFormTypeServices(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('form.extension')) {
            return;
        }

        $config = $container->getParameter('knd_rad.auto_inject.form_type');
        $types   = $container->getDefinition('form.extension')->getArgument(1);

        foreach($this->getClasses($config['dirs']) as $class)
        {
            $classContainerParameter = $this->injectClassContainerParameter($container, $class);

            $reflClass = $this->reflectionFactory->createReflectionClass($class);

            if($reflClass->isAbstract())
            {
                continue;
            }

            if ($reflClass->implementsInterface('Symfony\Component\Form\FormTypeInterface')) {

                    if (!strpos($class, $this->bundle->getNamespace()) === 0) {
                        continue;
                    }

                    $id = $this->serviceIdGenerator->generateFormTypeId($this->bundle, $class);

                    if ($container->hasDefinition($id)) {
                        continue;
                    }

                    $def = $this->definitionFactory->createFormTypeDefinition($class, $classContainerParameter);

                    $container->setDefinition($id, $def);

                    $alias = $this->serviceIdGenerator->generateFormTypeAlias($this->bundle, $class);

                    $types[$alias] = $id;
            }

        }

        $container->getDefinition('form.extension')->replaceArgument(1, $types);
    }

    protected function injectCommonServices(ContainerBuilder $container)
    {
        $config = $container->getParameter('knd_rad.auto_inject.common');

        foreach($this->getClasses($config['dirs']) as $class)
        {
            $reflClass = $this->reflectionFactory->createReflectionClass($class);

            if($reflClass->isAbstract())
            {
                continue;
            }

            if (!strpos($class, $this->bundle->getNamespace()) === 0) {
                continue;
            }

            $id = $this->serviceIdGenerator->generateServiceId($this->bundle, $class);

            if ($container->hasDefinition($id)) {
                continue;
            }

            $classContainerParameter = $this->injectClassContainerParameter($container, $class);
            $def = $this->definitionFactory->createDefinition($class, $classContainerParameter);

            $container->setDefinition($id, $def);
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

        $this->injectFormTypeServices($container);

    }
}
