<?php

/*
 * This file is part of the TaskRabbitMqBundle.
 *
 * (c) Yonel Ceruto <yonelceruto@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yceruto\TaskRabbitMqBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Parser;
use Yceruto\TaskRabbitMqBundle\DependencyInjection\TaskRabbitMqExtension;

class TaskRabbitMqExtensionTest extends TestCase
{
    /** @var ContainerBuilder */
    private $container;

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testTaskLoadThrowsExceptionUnlessTaskClassSet()
    {
        $loader = new TaskRabbitMqExtension();
        $config = $this->getEmptyConfig();
        unset($config['task_class']);
        $loader->load(array($config), new ContainerBuilder());
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testTaskLoadThrowsExceptionUnlessProducerSet()
    {
        $loader = new TaskRabbitMqExtension();
        $config = $this->getEmptyConfig();
        unset($config['producer']);
        $loader->load(array($config), new ContainerBuilder());
    }

    public function testTaskLoadModelClass()
    {
        $this->createFullConfiguration();

        $this->assertParameter('AppBundle\Entity\Task', 'task_rabbit_mq.task_class');
    }

    public function testTaskLoadDebug()
    {
        $this->createFullConfiguration();

        $this->assertParameter(true, 'task_rabbit_mq.debug');
        $this->assertHasDefinition('task_rabbit_mq.data_collector');
    }

    public function testTaskLoadNotDebug()
    {
        $this->container = new ContainerBuilder();
        $loader = new TaskRabbitMqExtension();
        $config = $this->getEmptyConfig();
        $config['debug'] = false;
        $loader->load(array($config), $this->container);

        $this->assertParameter(false, 'task_rabbit_mq.debug');
        $this->assertNotHasDefinition('task_rabbit_mq.data_collector');
    }

    public function testTaskLoadProducer()
    {
        $this->createFullConfiguration();

        $this->assertAlias('old_sound_rabbit_mq.task_producer', 'task_rabbit_mq.producer');
    }

    public function testTaskLoadManagerClassWithDefaults()
    {
        $this->createEmptyConfiguration();

        $this->assertParameter(null, 'task_rabbit_mq.model_manager_name');
    }

    public function testUserLoadManagerClass()
    {
        $this->createFullConfiguration();

        $this->assertParameter('custom', 'task_rabbit_mq.model_manager_name');
    }

    public function testTaskLoadManagementWithDefaults()
    {
        $this->createEmptyConfiguration();

        $definition = $this->container->getDefinition('task_rabbit_mq.management');
        $this->assertSame('http://127.0.0.1:15672', $definition->getArgument(0));
        $this->assertSame('guest', $definition->getArgument(1));
        $this->assertSame('guest', $definition->getArgument(2));
        $this->assertSame('/', $definition->getArgument(3));
    }

    public function testTaskLoadManagement()
    {
        $this->createFullConfiguration();

        $definition = $this->container->getDefinition('task_rabbit_mq.management');
        $this->assertSame('http://127.0.0.1:12372', $definition->getArgument(0));
        $this->assertSame('symfony', $definition->getArgument(1));
        $this->assertSame('p4ss', $definition->getArgument(2));
        $this->assertSame('/dev', $definition->getArgument(3));
    }

    public function testTaskLoadServiceWithDefaults()
    {
        $this->createEmptyConfiguration();

        $this->assertAlias('task_rabbit_mq.manager.default', 'task_rabbit_mq.manager');
        $this->assertAlias('task_rabbit_mq.assigner.default', 'task_rabbit_mq.assigner');
        $this->assertAlias('task_rabbit_mq.consumer.default', 'task_rabbit_mq.consumer');
    }

    public function testTaskLoadService()
    {
        $this->createFullConfiguration();

        $this->assertAlias('custom_task_manager', 'task_rabbit_mq.manager');
        $this->assertAlias('custom_task_assigner', 'task_rabbit_mq.assigner');
        $this->assertAlias('custom_task_consumer', 'task_rabbit_mq.consumer');
    }

    public function testTaskLoadLoadBalancerWithDefaults()
    {
        $this->createEmptyConfiguration();

        $this->assertParameter(null, 'task_rabbit_mq.load_balancer.consumer_type');
        $this->assertParameter(null, 'task_rabbit_mq.load_balancer.consumer_name');
        $this->assertParameter(10, 'task_rabbit_mq.load_balancer.delay');
    }

    public function testTaskLoadLoadBalancer()
    {
        $this->createFullConfiguration();

        $this->assertParameter('multiple_consumers', 'task_rabbit_mq.load_balancer.consumer_type');
        $this->assertParameter('tasks', 'task_rabbit_mq.load_balancer.consumer_name');
        $this->assertParameter(5, 'task_rabbit_mq.load_balancer.delay');
    }

    protected function createEmptyConfiguration()
    {
        $this->container = new ContainerBuilder();
        $loader = new TaskRabbitMqExtension();
        $config = $this->getEmptyConfig();
        $loader->load(array($config), $this->container);
        $this->assertTrue($this->container instanceof ContainerBuilder);
    }

    protected function createFullConfiguration()
    {
        $this->container = new ContainerBuilder();
        $loader = new TaskRabbitMqExtension();
        $config = $this->getFullConfig();
        $loader->load(array($config), $this->container);
        $this->assertTrue($this->container instanceof ContainerBuilder);
    }

    /**
     * getEmptyConfig.
     *
     * @return array
     */
    protected function getEmptyConfig()
    {
        $yaml = <<<EOF
task_class: AppBundle\Entity\Task
producer: old_sound_rabbit_mq.task_producer
EOF;
        $parser = new Parser();

        return $parser->parse($yaml);
    }

    /**
     * @return mixed
     */
    protected function getFullConfig()
    {
        $yaml = <<<EOF
task_class: AppBundle\Entity\Task
producer: old_sound_rabbit_mq.task_producer
debug: true
doctrine:
    model_manager_name: custom
management:
    url: http://127.0.0.1:12372
    user: symfony
    password: p4ss
    vhost: /dev
service:
    task_manager: custom_task_manager
    task_assigner: custom_task_assigner
    task_consumer: custom_task_consumer
load_balancer:
    consumer_type: multiple_consumers
    consumer_name: tasks
    delay: 5
EOF;
        $parser = new Parser();

        return $parser->parse($yaml);
    }

    protected function tearDown()
    {
        unset($this->container);
    }

    private function assertAlias($value, $key)
    {
        $this->assertSame($value, (string) $this->container->getAlias($key), sprintf('%s alias is correct', $key));
    }

    private function assertParameter($value, $key)
    {
        $this->assertSame($value, $this->container->getParameter($key), sprintf('%s parameter is correct', $key));
    }

    private function assertHasDefinition($id)
    {
        $this->assertTrue(($this->container->hasDefinition($id) ?: $this->container->hasAlias($id)));
    }

    private function assertNotHasDefinition($id)
    {
        $this->assertFalse(($this->container->hasDefinition($id) ?: $this->container->hasAlias($id)));
    }
}
