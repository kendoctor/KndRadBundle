<?php

namespace Knd\Bundle\RadBundle\DependencyInjection;

use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\DependencyInjection\Container;

class ServiceIdGenerator
{
    /**
     *  AppBundle\Manager\UserManager => 'app.manager.user'
     *  AppBundle\Manager\Sub\UserManager => 'app.manager.sub_user'
     *  AppBundle\Manager\Sub\UserManage => 'app.manager.sub_user_manage'
     *
     *
     * @param $bundleClass
     * @return mixed
     */
    protected function makeParentDirAsClassifier($bundleClass, $removeClassifier = false)
    {
        $return = strtolower($bundleClass);
        $classifierToken = null;

        $tokens = explode('\\', $bundleClass);
        if(count($tokens) > 1)
        {
            $classifierToken = array_shift($tokens);
            if(!$removeClassifier) {
                $lastToken = array_pop($tokens);
                if ($classifierToken != $lastToken) {
                    $lastToken = str_replace($classifierToken, '', $lastToken);
                    array_push($tokens, $lastToken);
                }
                $return = sprintf('%s.%s', $classifierToken, implode($tokens, '\\'));
            }
            else
            {
                $return = implode($tokens, '\\');
            }
        }

        return str_replace('\\', '_', Container::underscore($return));
    }

    public function generateClassContainerParameter(BundleInterface $bundle, $className)
    {
        $namespace = $bundle->getNamespace();
        $alias = $bundle->getAlias();

        $bundleClass = substr($className, strlen($namespace) + 1);

        return sprintf('%s.class.%s', $alias, $this->makeParentDirAsClassifier($bundleClass));

    }

    public function generateClassManagerId(BundleInterface $bundle, $className)
    {
        $namespace = $bundle->getNamespace();
        $alias = $bundle->getAlias();

        $bundleClass = substr($className, strlen($namespace) + 1);

        return sprintf('%s.manager.%s', $alias, $this->makeParentDirAsClassifier($bundleClass, true));
    }

    public function generateClassRepositoryId(BundleInterface $bundle, $className)
    {
        $namespace = $bundle->getNamespace();
        $alias = $bundle->getAlias();

        $bundleClass = substr($className, strlen($namespace) + 1);

        return sprintf('%s.repository.%s', $alias, $this->makeParentDirAsClassifier($bundleClass, true));
    }

    public function generateServiceId(BundleInterface $bundle, $className)
    {
        $namespace = $bundle->getNamespace();
        $alias = $bundle->getAlias();

        $bundleClass = substr($className, strlen($namespace) + 1);

        return sprintf('%s.%s', $alias, $this->makeParentDirAsClassifier($bundleClass));
    }

}
