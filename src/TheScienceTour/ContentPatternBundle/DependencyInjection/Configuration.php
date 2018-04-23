<?php
/**
 * Created by PhpStorm.
 * User: ferdydurke
 * Date: 11/10/2017
 * Time: 12:41
 */

namespace TheScienceTour\ContentPatternBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package TheScienceTour\ContentPatternBundle\DependencyInjection
 */
class Configuration implements ConfigurationInterface
{

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('the_science_tour_content_pattern');

        $rootNode
            ->children()
                ->arrayNode('presentations')
                    ->info('Paramétrage pour l’affichage des contenus' )
                    ->prototype('array')
                        ->children()
                            ->scalarNode('label')
                                ->info('L’étiquette à afficher sur l’onglet de la page du contenu' )
                            ->end()
                            ->scalarNode('template')
                                ->info('Le ssquelette Twig pour présenter le contenu' )
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

    }


}