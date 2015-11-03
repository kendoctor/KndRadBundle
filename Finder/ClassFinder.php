<?php

namespace Knd\Bundle\RadBundle\Finder;

use Knd\Bundle\RadBundle\Reflection\ReflectionFactory;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;

class ClassFinder
{
    private $filesystem;
    private $reflectionFactory;

    public function __construct(Filesystem $filesystem = null, ReflectionFactory $reflectionFactory = null)
    {
        $this->filesystem = $filesystem ?: new Filesystem();
        $this->reflectionFactory = $reflectionFactory ?: new ReflectionFactory();
    }



    public function findClasses($directory, $namespace, $ignoreSuffix = array())
    {
        if (false === $this->filesystem->exists($directory)) {
            return array();
        }

        $classes = array();

        $finder = new Finder();
        $finder->files();
        if(count($ignoreSuffix))
        {
            $finder->name(sprintf('/(?<!%s)\.php$/', implode('|', $ignoreSuffix)));
        }
        else
        {
            $finder->name('*.php');
        }

        $finder->in($directory);

        foreach ($finder->getIterator() as $name) {
            $baseName = substr($name, strlen($directory)+1, -4);
            $baseClassName = str_replace('/', '\\', $baseName);

            $classes[] = $namespace.'\\'.$baseClassName;
        }

        return $classes;
    }

    public function findClassesMatching($directory, $namespace, $pattern)
    {
        $pattern = sprintf('#%s#', str_replace('#', '\#', $pattern));
        $matches = function ($path) use ($pattern) { return preg_match($pattern, $path); };

        return array_values(array_filter($this->findClasses($directory, $namespace), $matches));
    }

    public function filterClassesImplementing(array $classes, $interface)
    {
        $reflectionFactory = $this->reflectionFactory;

        return array_filter($classes, function ($class) use ($interface, $reflectionFactory) {
            return $reflectionFactory->createReflectionClass($class)->isSubclassOf($interface);
        });
    }
}
