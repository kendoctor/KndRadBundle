<?php
/**
 * Created by PhpStorm.
 * User: kendoctor
 * Date: 15/11/2
 * Time: 下午12:51
 */

namespace Knd\Bundle\RadBundle;

use Knd\Bundle\RadBundle\DependencyInjection\Compiler\VoterStackPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class KndRadBundle extends  Bundle {

    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new VoterStackPass());
    }

}