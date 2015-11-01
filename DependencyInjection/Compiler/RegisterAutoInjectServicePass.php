<?php

namespace Knd\Bundle\RadBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class RegisterAutoInjectServicePass implements CompilerPassInterface
{
    private $bundle;

    public function __construct(BundleInterface $bundle)
    {
        $this->bundle = $bundle;
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

        $directory = $this->bundle->getPath().'/Entity';
        $namespace = $this->bundle->getNamespace().'\Entity';

        $classes = $this->classFinder->findClassesMatching($directory, $namespace, '(?<!Repository)$');
        foreach ($classes as $class) {
            if (!strpos($class, $this->bundle->getNamespace()) === 0) {
                continue;
            }
            $id = $this->serviceIdGenerator->generateForBundleClass($this->bundle, $class, 'repository');
            if ($container->hasDefinition($id)) {
                continue;
            }
            $def = $this->definitionFactory->createDefinition($class);
            $container->setDefinition($id, $def);
        }

    }
}
