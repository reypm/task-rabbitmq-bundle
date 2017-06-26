<?php

/*
 * This file is part of the TaskRabbitMqBundle.
 *
 * (c) Yonel Ceruto <yonelceruto@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yceruto\TaskRabbitMqBundle\Tests\DependencyInjection\Compiler;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Yceruto\TaskRabbitMqBundle\DependencyInjection\Compiler\AddWorkerCompilerPass;

class AddWorkerCompilerPassTest extends TestCase
{
    public function testProcess()
    {
        $container = new ContainerBuilder();

        $definition = $container->register('task_rabbit_mq.worker_container');
        $container->register('my_worker_service1')->addTag('task_rabbit_mq.worker');
        $container->register('my_worker_service2')->addTag('task_rabbit_mq.worker');
        $container->register('my_worker_service3')->addTag('task_rabbit_mq.worker');

        $workerPass = new AddWorkerCompilerPass();
        $workerPass->process($container);

        $expected = array(
            array('add', array('my_worker_service1', new Reference('my_worker_service1'))),
            array('add', array('my_worker_service2', new Reference('my_worker_service2'))),
            array('add', array('my_worker_service3', new Reference('my_worker_service3'))),
        );
        $this->assertEquals($expected, $definition->getMethodCalls());
    }

    public function testNotProcess()
    {
        $container = new ContainerBuilder();

        $workerPass = new AddWorkerCompilerPass();
        $workerPass->process($container);

        $this->assertFalse($container->hasDefinition('task_rabbit_mq.worker_container'));
    }
}
