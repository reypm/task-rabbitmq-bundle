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

use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Yaml\Parser;
use Yceruto\TaskRabbitMqBundle\Assigner\TaskAssigner;
use Yceruto\TaskRabbitMqBundle\DependencyInjection\TaskRabbitMqExtension;
use Yceruto\TaskRabbitMqBundle\Worker\WorkerContainer;

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

        $this->assertHasDefinition('task_rabbit_mq.data_collector');
    }

    public function testTaskLoadNotDebug()
    {
        $this->container = new ContainerBuilder();
        $loader = new TaskRabbitMqExtension();
        $config = $this->getEmptyConfig();
        $config['debug'] = false;
        $loader->load(array($config), $this->container);

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
        $config = $this->getEmptyConfig();

        $loader = new TaskRabbitMqExtension();
        $loader->load(array($config), $this->container);

        $this->assertTrue($this->container instanceof ContainerBuilder);
    }

    protected function createFullConfiguration()
    {
        $workerContainer = $this->getMockBuilder(WorkerContainer::class)->getMock();
        $producer = $this->getMockForAbstractClass(ProducerInterface::class);

        $this->container = new ContainerBuilder();
        $this->container->setDefinition('custom_task_assigner', new Definition(TaskAssigner::class, [$workerContainer, $producer, []]));

        $config = $this->getFullConfig();
        $loader = new TaskRabbitMqExtension();
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
rabbit_mq:
    routing_keys: ['queue1', 'queue2', 'queue3']
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
