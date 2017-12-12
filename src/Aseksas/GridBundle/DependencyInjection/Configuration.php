<?php

namespace Aseksas\GridBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('aseksas_grid');
        $rootNode->children()
            ->arrayNode('template')
                ->children()
                    ->scalarNode('layout')->defaultValue('')->end()
                    ->scalarNode('header')->defaultValue('')->end()
                    ->scalarNode('content')->defaultValue('')->end()
                    ->scalarNode('footer')->defaultValue('')->end()
                ->end()
            ->end()
            ->arrayNode('limits')->treatNullLike([])->prototype('scalar')->end()->defaultValue([10,25,50,100])->end()
            ->integerNode('limit')->defaultValue(25)->end()
            ->end();

        return $treeBuilder;
    }
}
