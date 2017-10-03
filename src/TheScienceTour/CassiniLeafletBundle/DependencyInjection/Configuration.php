<?php
/**
 * Created by PhpStorm.
 * User: ferdydurke
 * Date: 03/10/2017
 * Time: 12:18
 */

namespace TheScienceTour\CassiniLeafletBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('the_science_tour_cassini_leaflet');

        $rootNode
            ->children()
                ->arrayNode('default_location')
                    ->children()
                        ->floatNode('latitude')
                            ->defaultValue(48.0)
                            ->min(0.0)
                            ->info('the latitude of the default center of the maps')
                        ->end()
                       ->floatNode('longitude')
                            ->defaultValue(2.0)
                            ->min(0.0)
                        ->end()
                        ->integerNode('zoom')
                            ->defaultValue(11)
                           ->min(1)
                            ->max(18)
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->validate()
                ->ifTrue(function($v) {
                    return ($v['default_location']['zoom'] <= 0 || $v['default_location']['zoom'] > 18);
                })
                ->thenInvalid('The value of the zoom must be inside the range [1,18]');

        return $treeBuilder;
    }

}