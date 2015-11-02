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


    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
//        if (!$container->getParameter('knp_rad.detect.entity')) {
//            return;
//        }

        if (false === $container->hasDefinition('doctrine')) {
            return;
        }

        $includeDirs = array(
            'Entity',
            'Manager',
            'Repository',
            'Builder',
            'Form'
        );

        foreach($includeDirs as $dir)
        {
            $directory = sprintf("%s/%s", $this->bundle->getPath(), $dir);
            $namespace = sprintf("%s\\%s", $this->bundle->getNamespace(), $dir);
            $classes = $this->classFinder->findClasses($directory, $namespace);

            foreach ($classes as $class) {
                $classContainerParameter = null;

                $reflClass = $this->reflectionFactory->createReflectionClass($class);
                if($reflClass->implementsInterface('Knd\Bundle\RadBundle\TagInterface\AutoInjectClassParameterInterface'))
                {
                    if (!strpos($class, $this->bundle->getNamespace()) === 0) {
                        continue;
                    }
                    $classContainerParameter = $this->serviceIdGenerator->generateClassContainerParameter($this->bundle, $class);

                    if ($container->hasParameter($classContainerParameter)) {
                        continue;
                    }

                    $container->setParameter($classContainerParameter, $class);
                }

                if ($reflClass->implementsInterface('Knd\Bundle\RadBundle\TagInterface\AutoInjectServiceInterface')) {

                    if (!strpos($class, $this->bundle->getNamespace()) === 0) {
                        continue;
                    }

                    $id = $this->serviceIdGenerator->generateServiceId($this->bundle, $class);

                    if ($container->hasDefinition($id)) {
                        continue;
                    }

                    $def = $this->definitionFactory->createDefinition($classContainerParameter);

                    $container->setDefinition($id, $def);

                }

                if ($reflClass->implementsInterface('Knd\Bundle\RadBundle\TagInterface\AutoInjectDoctrineRepositoryInterface')) {

                    if (!strpos($class, $this->bundle->getNamespace()) === 0) {
                        continue;
                    }
                    $id = $this->serviceIdGenerator->generateClassRepositoryId($this->bundle, $class);

                    if ($container->hasDefinition($id)) {
                        continue;
                    }

                    $def = $this->definitionFactory->createDoctrineRepositoryDefinition($classContainerParameter);

                    $container->setDefinition($id, $def);

                }

                if ($reflClass->implementsInterface('Knd\Bundle\RadBundle\TagInterface\AutoInjectManagerByFactoryInterface')) {

                    if (!strpos($class, $this->bundle->getNamespace()) === 0) {
                        continue;
                    }

                    $id = $this->serviceIdGenerator->generateClassManagerId($this->bundle, $class);

                    if ($container->hasDefinition($id)) {
                        continue;
                    }

                    $def = $this->definitionFactory->createClassManagerDefinition($classContainerParameter);

                    $container->setDefinition($id, $def);

                }

            }

        }

    }
}
