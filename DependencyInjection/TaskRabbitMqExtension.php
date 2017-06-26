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
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class TaskRabbitMqExtension extends Extension
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
        $container->setParameter('task_rabbit_mq.debug', $config['debug']);

        $container->setAlias('task_rabbit_mq.producer', $config['producer']);

        // Load Doctrine configuration
        $container->setParameter('task_rabbit_mq.model_manager_name', $config['doctrine']['model_manager_name']);

        // Load RabbitMq Management configuration
        $container->setParameter('task_rabbit_mq.management.url', $config['management']['url']);
        $container->setParameter('task_rabbit_mq.management.user', $config['management']['user']);
        $container->setParameter('task_rabbit_mq.management.password', $config['management']['password']);
        $container->setParameter('task_rabbit_mq.management.vhost', $config['management']['vhost']);

        // Load Service configuration
        $container->setAlias('task_rabbit_mq.manager', $config['service']['task_manager']);
        $container->setAlias('task_rabbit_mq.assigner', $config['service']['task_assigner']);
        $container->setAlias('task_rabbit_mq.consumer', $config['service']['task_consumer']);

        // Load Load-Balancer Configuration
        $container->setParameter('task_rabbit_mq.load_balancer.consumer_type', $config['load_balancer']['consumer_type']);
        $container->setParameter('task_rabbit_mq.load_balancer.consumer_name', $config['load_balancer']['consumer_name']);
        $container->setParameter('task_rabbit_mq.load_balancer.delay', $config['load_balancer']['delay']);
    }
}
