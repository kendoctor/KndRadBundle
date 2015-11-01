<?php

namespace spec\Knd\Bundle\RadBundle\Manager;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ManagerFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Knd\Bundle\RadBundle\Manager\ManagerFactory');
    }
}
