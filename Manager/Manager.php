<?php

namespace Knd\Bundle\RadBundle\Manager;

use Doctrine\ORM\EntityManager;
use Knd\Bundle\RadBundle\Bundle\BundleGuesser;
use Knd\Bundle\RadBundle\DependencyInjection\ServiceIdGenerator;
use Knd\Bundle\RadBundle\Form\FormManager;
use Knd\Bundle\RadBundle\Repository\EntityRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class Manager
 * @package Knd\Bundle\RadBundle\Manager
 */
class Manager
{
    /**
     * @var
     */
    protected $class;

    /**
     * @var Container
     */
    protected $container;


    /**
     * @var ServiceIdGenerator
     */
    private $serviceIdGenerator;

    /**
     * @param $class
     * @param Container $container
     */
    public function __construct($class, Container $container)
    {
        $this->class = $class;
        $this->container = $container;

        $this->serviceIdGenerator = new ServiceIdGenerator();
    }


    /**
     * @return object
     */
    protected function getBundleGuesser()
    {
        return $this->get('knd_rad.bundle.guesser');
    }


    /**
     * @return mixed
     */
    protected function getBundle()
    {
        return $this->getBundleGuesser()->getBundleForClass($this->getClass());
    }

    /**
     * @param $object
     * @param null $purpose
     * @param array $options
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createForm($object, $purpose = null, $options = array())
    {
        return $this->getFormManger()->createObjectForm($object, $purpose, $options);
    }

    /**
     * @param $object
     * @param null $purpose
     * @param array $options
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createBoundForm($object, $purpose = null, $options = array())
    {
        return $this->getFormManger()->createBoundObjectForm($object, $purpose, $options);
    }

    /**
     * @param $entity
     * @param bool $flush
     */
    public function persist($entity, $flush = false)
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param $entity
     * @param bool $flush
     */
    public function remove($entity, $flush = false)
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->get('doctrine')->getManager();
    }

    /**
     * @return FormManager
     */
    public function getFormManger()
    {
        return $this->get('knd_rad.form.manager');
    }

    /**
     * @return EntityRepository
     */
    public function getRepository()
    {
        $repositoryServiceId = $this->serviceIdGenerator->generateClassRepositoryId($this->getBundle(), $this->getClass());

        return $this->get($repositoryServiceId);
    }

    /**
     * @return object
     */
    public function getPaginator()
    {
        return $this->get('knp_paginator');
    }

    /**
     * @return mixed
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return mixed
     */
    public function create()
    {
        return new $this->class;
    }


    /**
     * @param $criteria
     * @param $page
     * @param $limit
     * @return mixed
     */
    public function createPagination($criteria, $page, $limit)
    {
        return $this->getPaginator()
            ->paginate($this->getRepository()->createIndexQueryBuilder($criteria), $page, $limit);
    }

    /**
     * @param $serviceId
     * @return object
     */
    public function get($serviceId)
    {
        return $this->container->get($serviceId);
    }

    public function findBy($criteria = array())
    {
        $findMethod = is_scalar($criteria) ? 'find' : 'findOneBy';
        $repository = $this->getRepository();
        return $repository->$findMethod($criteria);
    }

    public function findOr404($criteria = array())
    {
        if ($result = $this->findBy($criteria)) {
            return $result;
        }
        throw new NotFoundHttpException;
    }

}
