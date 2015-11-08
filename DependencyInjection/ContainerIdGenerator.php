<?php

namespace Knd\Bundle\RadBundle\DependencyInjection;

/**
 * Class ContainerIdGenerator
 * @package Knd\Bundle\RadBundle\DependencyInjection
 */
class ContainerIdGenerator
{
    /**
     * @param $id
     * @return string
     */
    public static function underscore($id)
    {
        return strtolower(preg_replace(array('/([A-Z]+)([A-Z][a-z])/', '/([a-z\d])([A-Z])/'), array('\\1_\\2', '\\1_\\2'), strtr($id, '_', '.')));
    }


    /**
     * @param $entityClass
     * @return string
     */
    public function getEntityRepositoryServiceId($entityClass)
    {
        return $this->getIdentifier($entityClass, 'repository');
    }

    /**
     * @param $entityClass
     * @return string
     */
    public function getManagerServiceId($entityClass)
    {
        return $this->getIdentifier($entityClass, 'manager');
    }

    /**
     * @param $class
     * @return string
     * @throws \Exception
     */
    public function getServiceId($class)
    {
        $bundleClass = $this->getBundleClass($class);

        $classPart = $this->getClassWithoutBundleNamespace($bundleClass, $class);

        $tokens = explode('\\', static::underscore($classPart));

        $suffix = sprintf('%s.%s',$tokens[0], implode('_', array_slice($tokens, 1)));

        $suffix = preg_replace(sprintf('/_%s$/', $tokens[0]), '', $suffix);

        return sprintf('%s.%s', $this->getBundlePrefix($bundleClass),  $suffix);
    }

    /**
     * @param $class
     * @param $classifier
     * @param bool $removeFirst
     * @return string
     * @throws \Exception
     */
    protected function getIdentifier($class, $classifier, $removeFirst = true)
    {
        $bundleClass = $this->getBundleClass($class);

        $classPart = $this->getClassWithoutBundleNamespace($bundleClass, $class);

        $tokens = explode('\\', static::underscore($classPart));

        if($removeFirst)
        {
            $suffix =  implode('_', array_slice($tokens, 1));
        }
        else
        {
            $suffix = sprintf('%s.%s',$tokens[0], implode('_', array_slice($tokens, 1)));
        }

        $suffix = preg_replace(sprintf('/_%s$/', $tokens[0]), '', $suffix);

        return sprintf('%s.%s.%s', $this->getBundlePrefix($bundleClass), $classifier, $suffix);
    }

    /**
     * @param $bundleClass
     * @return mixed
     */
    protected function getBundlePrefix($bundleClass)
    {
        return str_replace(array('\\', '_bundle'), array('_', ''), static::underscore($bundleClass));
    }

    /**
     * @param $bundleClass
     * @param $class
     * @return mixed
     */
    protected function getClassWithoutBundleNamespace($bundleClass, $class)
    {

        return str_replace(sprintf('%s\\', $bundleClass), '' , $class);
    }

    /**
     * @param $class
     * @return mixed
     * @throws \Exception
     */
    public function getBundleClass($class)
    {

        if(preg_match('/(^.+\\Bundle\\[^\\]+Bundle)|(^.+Bundle)/', $class, $matches))
        {
            //check the bundle class exists and valid
            return $matches[0];
        }

        throw new \Exception(sprintf('Failure to get bundle class for %s', $class));
    }

    /**
     * @param $class
     * @return string
     */
    public function getContainerParameter($class)
    {
        return $this->getIdentifier($class, 'class', false);
    }

    /**
     * @param $class
     * @param null $dir
     * @return string
     * @throws \Exception
     */
    public function guessEntityRepositoryClass($class, $dir = null)
    {
        if($dir)
        {
            $dir = preg_replace('/\//', '\\', $dir);
            $bundleClass = $this->getBundleClass($class);
            $classPart = $this->getClassWithoutBundleNamespace($bundleClass, $class);

            return sprintf('%s\\%s%s',
                $bundleClass,
                preg_replace('/^[^\\\\]+/', $dir, $classPart),
                'Repository'
            );

        }

        return sprintf('%sRepository', $class);

    }

    /**
     * @param $class
     * @param string $dir
     * @return string
     * @throws \Exception
     */
    public function guessManagerClass($class, $dir = "Manager")
    {
        $bundleClass = $this->getBundleClass($class);
        $classPart = $this->getClassWithoutBundleNamespace($bundleClass, $class);

        return sprintf('%s\\%s%s',
            $bundleClass,
            preg_replace('/^[^\\\\]+/', $dir, $classPart),
            'Manager'
        );
    }

    /**
     * @param $class
     * @return string
     * @throws \Exception
     */
    public function getFormTypeAlias($class)
    {
        $bundleClass = $this->getBundleClass($class);

        $bundlePrefix = $this->getBundlePrefix($bundleClass);

        $classPart = $this->getClassWithoutBundleNamespace($bundleClass, $class);

        $classPart = static::underscore($classPart);
        $classPart = preg_replace('/\\\\/', '_', $classPart);
        $classPart = preg_replace('/^form_(type_){0,1}/', '', $classPart);
        $classPart = preg_replace('/_type$/', '', $classPart) ;

        return sprintf('%s_%s', $bundlePrefix, $classPart);
    }

    /**
     * @param $class
     * @return string
     * @throws \Exception
     */
    public function getFormTypeServiceId($class)
    {
        $bundleClass = $this->getBundleClass($class);

        $bundlePrefix = $this->getBundlePrefix($bundleClass);

        $classPart = $this->getClassWithoutBundleNamespace($bundleClass, $class);

        $classPart = static::underscore($classPart);
        $classPart = preg_replace('/\\\\/', '_', $classPart);
        $classPart = preg_replace('/^form_(type_){0,1}/', '', $classPart);
        $classPart = preg_replace('/_type$/', '', $classPart) ;

        return sprintf('%s.form.type.%s', $bundlePrefix, $classPart);
    }

    public function getActionRolePrefix($class)
    {
        $bundleClass = $this->getBundleClass($class);

        $bundlePrefix = $this->getBundlePrefix($bundleClass);

        $classPart = $this->getClassWithoutBundleNamespace($bundleClass, $class);

        $classPart = static::underscore($classPart);

        $classPart = preg_replace('/\\\\/', '_', $classPart);

        return sprintf('%s_%s', $bundlePrefix, $classPart);
    }

    public function getModelVoterServiceId($class)
    {
        $bundleClass = $this->getBundleClass($class);

        $bundlePrefix = $this->getBundlePrefix($bundleClass);

        $classPart = $this->getClassWithoutBundleNamespace($bundleClass, $class);

        $classPart = static::underscore($classPart);
        $classPart = preg_replace('/\\\\/', '_', $classPart);
        $classPart = preg_replace('/^security_(voter_){0,1}/', '', $classPart);
        $classPart = preg_replace('/_voter$/', '', $classPart) ;

        return sprintf('%s.security.voter.%s', $bundlePrefix, $classPart);
    }

    public function guessModelVoterClass($class, $dir = 'Security/Voter')
    {
        if($dir)
        {
            $dir = preg_replace('/\//', '\\', $dir);
            $bundleClass = $this->getBundleClass($class);
            $classPart = $this->getClassWithoutBundleNamespace($bundleClass, $class);

            return sprintf('%s\\%s%s',
                $bundleClass,
                preg_replace('/^[^\\\\]+/', $dir, $classPart),
                'Voter'
            );

        }

        return sprintf('%sVoter', $class);
    }

}
