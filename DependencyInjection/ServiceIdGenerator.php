<?php

namespace Knd\Bundle\RadBundle\DependencyInjection;

use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\DependencyInjection\Container;

class ServiceIdGenerator
{
    public function generateClassContainerParameter(BundleInterface $bundle, $className)
    {
        $namespace = $bundle->getNamespace();
        $alias = $bundle->getAlias();

        $bundleClass = substr($className, strlen($namespace) + 1);

        $bundlePart = str_replace('\\', '_', Container::underscore($bundleClass));

        return sprintf('%s.class.%s', $alias, $bundlePart);

    }

    public function generateForBundleClass(BundleInterface $bundle, $className, $withSuffix = false)
    {
        $namespace = $bundle->getNamespace();
        $extension = $bundle->getContainerExtension();

        $extensionAlias = $bundle->getAlias();


        $bundleClass = substr($className, strlen($namespace) + 1);

        $bundlePart = str_replace('\\', '.', Container::underscore($bundleClass));

        if (false !== $withSuffix) {
            $bundlePart .= '_'.$withSuffix;
        }

        return sprintf('%s.%s', $extensionAlias, $bundlePart);
    }
}
