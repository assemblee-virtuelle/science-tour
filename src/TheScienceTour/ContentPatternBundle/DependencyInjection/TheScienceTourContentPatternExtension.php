<?php
/**
 * Created by PhpStorm.
 * User: ferdydurke
 * Date: 11/10/2017
 * Time: 12:54
 */

namespace TheScienceTour\ContentPatternBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class TheScienceTourContentPatternExtension
 * @package TheScienceTour\ContentPatternBundle\DependencyInjection
 */
class TheScienceTourContentPatternExtension extends Extension
{

    const templates = [
        [
            'label' => 'Description',
            'template' => 'ContentPattern:default:description.html.twig'
        ]
    ];

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $configuration = new Configuration();

        //$config = $processor->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        //$loader->load('services.yml');

    }

    /*
    public function getAlias() {
        return "cassini_leaflet";
    }
    */

}