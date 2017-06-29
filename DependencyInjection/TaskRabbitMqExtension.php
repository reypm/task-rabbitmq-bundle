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

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class TaskRabbitMqExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(array(__DIR__.'/../Resources/config')));
        $loader->load('services.xml');

        $configuration = new Configuration($this->getAlias());
        $config = $this->processConfiguration($configuration, $configs);

        if ($config['debug']) {
            $loader->load('profiler.xml');
        }

        // Load Main Configuration
        $container->setParameter('task_rabbit_mq.task_class', $config['task_class']);

        // Load Doctrine configuration
        $container->setParameter('task_rabbit_mq.model_manager_name', $config['doctrine']['model_manager_name']);

        // Load Service configuration
        $container->setAlias('task_rabbit_mq.manager', $config['service']['task_manager']);
        $container->setAlias('task_rabbit_mq.assigner', $config['service']['task_assigner']);
        $container->setAlias('task_rabbit_mq.consumer', $config['service']['task_consumer']);

        // Load RabbitMq configuration
        $container->setAlias('task_rabbit_mq.producer', $config['rabbit_mq']['producer']);

        $definition = $container->findDefinition('task_rabbit_mq.assigner');
        $definition->replaceArgument(2, $config['rabbit_mq']['routing_keys']);

        // Load Load-Balancer Configuration
        $container->setParameter('task_rabbit_mq.load_balancer.consumer_type', $config['load_balancer']['consumer_type']);
        $container->setParameter('task_rabbit_mq.load_balancer.consumer_name', $config['load_balancer']['consumer_name']);
        $container->setParameter('task_rabbit_mq.load_balancer.delay', $config['load_balancer']['delay']);
    }

    /**
     * {@inheritdoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        if ($container->hasExtension('old_sound_rabbit_mq')) {
            // old_sound_rabbit_mq
            $oldSoundConfig = $container->getExtensionConfig('old_sound_rabbit_mq');
            $oldSoundConfig = current($oldSoundConfig);

            $oldSoundConfig['producers']['tasks']['exchange_options']['name'] = 'tasks';
            $oldSoundConfig['producers']['tasks']['exchange_options']['type'] = 'direct';
            $oldSoundConfig['consumers']['tasks']['exchange_options']['name'] = 'tasks';
            $oldSoundConfig['consumers']['tasks']['exchange_options']['type'] = 'direct';
            $oldSoundConfig['consumers']['tasks']['queue_options']['name'] = 'tasks';
            $oldSoundConfig['consumers']['tasks']['callback'] = 'task_rabbit_mq.consumer';
            $container->prependExtensionConfig('old_sound_rabbit_mq', $oldSoundConfig);

            // task_rabbit_mq
            $config['producer'] = 'old_sound_rabbit_mq.tasks_producer';
            if ($oldSoundConfig['connections']['default']['user']) {
                $config['management']['user'] = $oldSoundConfig['connections']['default']['user'];
            }
            if ($oldSoundConfig['connections']['default']['password']) {
                $config['management']['password'] = $oldSoundConfig['connections']['default']['password'];
            }
            if ($oldSoundConfig['connections']['default']['vhost']) {
                $config['management']['vhost'] = $oldSoundConfig['connections']['default']['vhost'];
            }
            $container->prependExtensionConfig('task_rabbit_mq', $config);
        }
    }
}
