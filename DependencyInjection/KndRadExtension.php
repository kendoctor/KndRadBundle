<?php
/**
 * Created by PhpStorm.
 * User: kendoctor
 * Date: 15/11/2
 * Time: 下午12:58
 */

namespace Knd\Bundle\RadBundle\DependencyInjection;


use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class KndRadExtension extends Extension {

    /**
     * Loads a specific configuration.
     *
     * @param array $configs An array of configuration values
     * @param ContainerBuilder $container A ContainerBuilder instance
     *
     */
    public function load(array $configs, ContainerBuilder $container)
    {

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('knd_rad.auto_inject.entity', $config['auto_inject']['entity']);
        $container->setParameter('knd_rad.auto_inject.form_type', $config['auto_inject']['form_type']);
        $container->setParameter('knd_rad.auto_inject.common', $config['auto_inject']['common']);


        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

    }
}