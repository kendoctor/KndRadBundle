<?php
/**
 * Created by PhpStorm.
 * User: kendoctor
 * Date: 15/11/7
 * Time: 下午9:37
 */

namespace Knd\Bundle\RadBundle\DependencyInjection\Compiler;


use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class VoterStackPass implements  CompilerPassInterface {

    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {

        if (!$container->has('knd_rad.security.voter.stack')) {
            return;
        }

        $definition = $container->findDefinition(
            'knd_rad.security.voter.stack'
        );

        $taggedServices = $container->findTaggedServiceIds(
            'knd_rad.security.voter'
        );

        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall(
                'addVoter',
                array(new Reference($id))
            );
        }
    }

}