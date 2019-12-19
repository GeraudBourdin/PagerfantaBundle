<?php

/*
 * This file is part of the Pagerfanta package.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BabDev\PagerfantaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration
 *
 * @author Julien Brochet <mewt@madalynn.eu>
 */
class Configuration implements ConfigurationInterface
{
    const EXCEPTION_STRATEGY_TO_HTTP_NOT_FOUND = 'to_http_not_found';

    /**
     * Generates the configuration tree builder.
     *
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('babdev_pagerfanta', 'array');

        if (method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            // BC layer for symfony/config 4.1 and older
            $rootNode = $treeBuilder->root('babdev_pagerfanta', 'array');
        }

        $rootNode
            ->children()
                ->scalarNode('default_view')->defaultValue('default')->end()
                ->arrayNode('exceptions_strategy')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('out_of_range_page')->defaultValue(self::EXCEPTION_STRATEGY_TO_HTTP_NOT_FOUND)->end()
                        ->scalarNode('not_valid_current_page')->defaultValue(self::EXCEPTION_STRATEGY_TO_HTTP_NOT_FOUND)->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
