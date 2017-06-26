<?php

/*
 * This file is part of the TaskRabbitMqBundle.
 *
 * (c) Yonel Ceruto <yonelceruto@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yceruto\TaskRabbitMqBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AddWorkerCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('task_rabbit_mq.worker_container')) {
            return;
        }

        $definition = $container->getDefinition('task_rabbit_mq.worker_container');

        foreach ($container->findTaggedServiceIds('task_rabbit_mq.worker') as $id => $attributes) {
            $definition->addMethodCall('add', array($id, new Reference($id)));
        }
    }
}
