<?php

namespace spec\Knd\Bundle\RadBundle\Bundle;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;


class BundleSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Knd\Bundle\RadBundle\Bundle\Bundle');
        $this->shouldHaveType('Symfony\Component\HttpKernel\Bundle\Bundle');
    }
}
