<?php

namespace spec\Knd\Bundle\RadBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ManagerSpec extends ObjectBehavior
{
    function let(EntityManagerInterface $entityManager, PaginatorInterface $paginator)
    {
        $this->beConstructedWith('spec\Knd\Bundle\RadBundle\Manager\Foo', $entityManager, $paginator);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Knd\Bundle\RadBundle\Manager\Manager');
    }

    function it_should_return_EntityRepository_instance_for_getRepository()
    {
        $this->getRepository()->shouldHaveType('Doctrine\ORM\EntityRepository');
    }

    function it_should_return_null_for_getClass()
    {
        $this->getClass()->shouldBe('spec\Knd\Bundle\RadBundle\Manager\Foo');
    }

    function it_should_create_instance_of_set_class()
    {
        $this->create()->shouldHaveType('spec\Knd\Bundle\RadBundle\Manager\Foo');
    }

//    function it_should_return_pagination_instance_for_createPagination()
//    {
//        $this->createPagination(
//            array(),
//            1,
//            10
//        )->shouldHaveType('Knp\Component\Pager\Pagination\PaginationInterface');
//    }


}

class Foo
{

}

