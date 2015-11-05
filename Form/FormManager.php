<?php

namespace Knd\Bundle\RadBundle\Form;

use Doctrine\Common\Inflector\Inflector;
use Knd\Bundle\RadBundle\Bundle\BundleGuesser;
use Knd\Bundle\RadBundle\Reflection\ClassMetadataFetcher;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormRegistryInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class FormManager
{
    private $requestStack;
    private $fetcher;
    private $factory;
    private $formRegistry;
    private $bundleGuesser;

    public function __construct(ClassMetadataFetcher $fetcher = null,
                                FormFactoryInterface $factory,
                                FormRegistryInterface $formRegistry,
                                BundleGuesser $bundleGuesser,
                                RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
        $this->fetcher         = $fetcher ?: new ClassMetadataFetcher;
        $this->factory         = $factory;
        $this->formRegistry    = $formRegistry;
        $this->bundleGuesser   = $bundleGuesser;
    }

    public function createObjectForm($object, $purpose = null, array $options = array())
    {
        $type = $this->getFormType($object, $purpose);

        if (null !== $type) {
            return $this->factory->create($type, $object, $options);
        }

        throw new \RuntimeException(sprintf('The form manager was unable to create the form. Please, make sure you have correctly registered one that fit your need.'));
    }

    public function createBoundObjectForm($object, $purpose = null, array $options = array())
    {
        if (!$this->getRequest()->isMethodSafe()) {
            $options = array_merge(array('method' => $this->getRequest()->getMethod()), $options);
        }
        $form = $this->createObjectForm($object, $purpose, $options);
        $form->handleRequest($this->getRequest());

        return $form;
    }


    private function getRequest()
    {
        return $this->requestStack->getCurrentRequest();
    }


    private function getFormType($object, $purpose = null)
    {
        $currentPurpose = $purpose ? $purpose.'_' : '';
        $bundle = $this->bundleGuesser->getBundleForClass($object);
        $bundleName = preg_replace('/Bundle$/', '', $bundle->getName());
        $bundleNamespace = $bundle->getNamespace();

        $alias = sprintf('%s_%s%s', strtolower($bundleName), $currentPurpose, strtolower($this->fetcher->getShortClassName($object)));

        $class = sprintf('%s\\Form\\%s%sType', $bundleNamespace, Inflector::classify($purpose), $this->fetcher->getShortClassName($object));

        if (null === $type = $this->loadFormType($alias, $class)) {
            $class = sprintf('%s\\Form\\Type\\%s%sType', $bundleNamespace, Inflector::classify($purpose), $this->fetcher->getShortClassName($object));

            $type = $this->loadFormType($alias, $class);
        }


        if(null === $type)
        {

                throw new \Exception(sprintf('%s class or form type with alias %s not exsits', $class, $alias));
        }


        return $type;
    }

    private function loadFormType($alias, $class)
    {
        $type = $this->getAlias($class, $alias);

        if (!$this->formRegistry->hasType($type)) {
            return null;
        }

        return $type;
    }

    private function getAlias($class, $default)
    {
        if (!class_exists($class)) {
            return $default;
        }

        try {
            return unserialize(sprintf('O:%d:"%s":0:{}', strlen($class), $class))->getName();
        } catch (\Exception $e) {
        }

        return $default;
    }
}
