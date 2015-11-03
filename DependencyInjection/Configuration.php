<?php
/**
 * Created by PhpStorm.
 * User: kendoctor
 * Date: 15/11/2
 * Time: 下午12:59
 */

namespace Knd\Bundle\RadBundle\DependencyInjection;


use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('knd_rad');

        $rootNode
            ->children()
                ->arrayNode('auto_inject')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('entity')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('dirs')
                                    ->defaultValue(array('Entity'))
                                    ->prototype('scalar')->end()
                                ->end()
                                ->booleanNode('manager')->defaultTrue()->end()
                                ->booleanNode('repository')->defaultTrue()->end()
                                ->arrayNode('ignore_suffix')
                                    ->defaultValue(array('Repository'))
                                    ->prototype('scalar')->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('form_type')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('dirs')
                                    ->defaultValue(array('Form'))
                                    ->prototype('scalar')->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('common')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('dirs')
                                    ->defaultValue(array('Manager'))
                                    ->prototype('scalar')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }

}