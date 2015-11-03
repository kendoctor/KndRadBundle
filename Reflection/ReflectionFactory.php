<?php

namespace Knd\Bundle\RadBundle\Reflection;

class ReflectionFactory
{
    public function createReflectionClass($class)
    {
        return new \ReflectionClass($class);
    }

    public function getConstructorParameters($class)
    {
        if(method_exists($class, '__construct'))
        {
            $r = new \ReflectionMethod($class, '__construct');
            return $r->getParameters();
        }

        return array();
    }

    public function getParameters($controller)
    {
        if (is_array($controller)) {
            $r = new \ReflectionMethod($controller[0], $controller[1]);
        } else {
            $r = new \ReflectionFunction($controller);
        }

        return $r->getParameters();
    }
}
