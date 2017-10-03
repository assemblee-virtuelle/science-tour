<?php
/**
 * Created by PhpStorm.
 * User: ferdydurke
 * Date: 03/10/2017
 * Time: 12:32
 */

namespace TheScienceTour\CassiniLeafletBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class TheScienceTourCassiniLeafletExtension extends Extension
{

    const DEFAULT_LATITUDE = 48;
    const DEFAULT_LONGITUDE = 2;
    const DEFAULT_ZOOM = 11;

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $configuration = new Configuration();

        $config = $processor->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        if (empty($config['default_location'])) {
            $container->setParameter('cassini_leaflet_latitude', self::DEFAULT_LATITUDE);
            $container->setParameter('cassini_leaflet_longitude', self::DEFAULT_LONGITUDE);
            $container->setParameter('cassini_leaflet_zoom', self::DEFAULT_ZOOM);
        } else {
            $container->setParameter('cassini_leaflet_latitude', $config['default_location']['latitude']);
            $container->setParameter('cassini_leaflet_longitude', $config['default_location']['longitude']);
            $container->setParameter('cassini_leaflet_zoom', $config['default_location']['zoom']);
        };
    }

    /*
    public function getAlias() {
        return "cassini_leaflet";
    }
    */
}