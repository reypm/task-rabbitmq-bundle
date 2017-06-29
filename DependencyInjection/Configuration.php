<?php

/*
 * This file is part of the TaskRabbitMqBundle.
 *
 * (c) Yonel Ceruto <yonelceruto@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yceruto\TaskRabbitMqBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    private $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $tree = new TreeBuilder();

        $rootNode = $tree->root($this->name);

        $rootNode
            ->children()
                ->scalarNode('task_class')->isRequired()->cannotBeEmpty()->end()
                ->booleanNode('debug')->defaultValue('%kernel.debug%')->end()
            ->end()
        ;

        $this->addRabbitMqSection($rootNode);
        $this->addDoctrineSection($rootNode);
        $this->addServiceSection($rootNode);
        $this->addLoadBalancerSection($rootNode);

        return $tree;
    }

    private function addRabbitMqSection(ArrayNodeDefinition $node)
    {
        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('rabbit_mq')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('url')->defaultValue('')->end()
                        ->scalarNode('producer')->isRequired()->cannotBeEmpty()->end()
                        ->arrayNode('routing_keys')
                            ->beforeNormalization()->ifString()->then(function ($v) { return array($v); })->end()
                            ->prototype('scalar')->end()
                            ->defaultValue(array())
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function addDoctrineSection(ArrayNodeDefinition $node)
    {
        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('doctrine')
                    ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('model_manager_name')->defaultNull()->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function addServiceSection(ArrayNodeDefinition $node)
    {
        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('service')
                    ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('task_manager')->defaultValue('task_rabbit_mq.manager.default')->end()
                            ->scalarNode('task_assigner')->defaultValue('task_rabbit_mq.assigner.default')->end()
                            ->scalarNode('task_consumer')->defaultValue('task_rabbit_mq.consumer.default')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addLoadBalancerSection(ArrayNodeDefinition $node)
    {
        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('load_balancer')
                    ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('consumer_type')->defaultNull()->end()
                            ->scalarNode('consumer_name')->defaultNull()->end()
                            ->scalarNode('delay')->defaultValue(10)->info('Seconds')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
