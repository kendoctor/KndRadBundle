<?php

namespace Knd\Bundle\RadBundle\Bundle;

use Knd\Bundle\RadBundle\DependencyInjection\Compiler\RegisterAppBundlePass;
use Knd\Bundle\RadBundle\DependencyInjection\Compiler\RegisterAutoInjectServicePass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle as BaseBundle;

class Bundle extends BaseBundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new RegisterAppBundlePass($this));
        $container->addCompilerPass(new RegisterAutoInjectServicePass($this));
    }

    public function getAlias()
    {
        $tmp = str_replace('Bundle', '', $this->getName());
        return ContainerBuilder::underscore($tmp);
    }

}
