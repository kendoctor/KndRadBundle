<?php

namespace Knd\Bundle\RadBundle\DependencyInjection\Compiler;

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

    public function __construct(BundleInterface $bundle, ClassFinder $classFinder = null,  $definitionFactory = null, ServiceIdGenerator $serviceIdGenerator = null)
    {
        $this->bundle             = $bundle;
        $this->classFinder        = $classFinder ?: new ClassFinder;
        $this->serviceIdGenerator = $serviceIdGenerator ?: new ServiceIdGenerator();
        $this->reflectionFactory  = new ReflectionFactory();
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
                $reflClass = $this->reflectionFactory->createReflectionClass($class);
                if($reflClass->implementsInterface('Knd\Bundle\RadBundle\TagInterface\AutoInjectClassParameterInterface'))
                {
                    if (!strpos($class, $this->bundle->getNamespace()) === 0) {
                        continue;
                    }
                    $id = $this->serviceIdGenerator->generateClassContainerParameter($this->bundle, $class, 'repository');

                    if ($container->hasParameter($id)) {
                        continue;
                    }

                    $container->setParameter($id, $class);
                }



            }

        }

    }
}
