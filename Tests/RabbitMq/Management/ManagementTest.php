<?php

/*
 * This file is part of the TaskRabbitMqBundle.
 *
 * (c) Yonel Ceruto <yonelceruto@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yceruto\TaskRabbitMqBundle\Tests\RabbitMq\Management;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Yceruto\TaskRabbitMqBundle\RabbitMq\Management\Management;
use Yceruto\TaskRabbitMqBundle\RabbitMq\Management\ManagementQueueResponse;

class ManagementTest extends TestCase
{
    /** @var Management */
    private $management;
    /** @var MockHandler */
    private $handler;

    public function setUp()
    {
        $this->handler = new MockHandler();
        $handler = HandlerStack::create($this->handler);
        $client = new Client(array('handler' => $handler));

        $this->management = new Management('http://localhost:15672', 'guest', 'guest', '/');
        $this->management->setClientHttp($client);
    }

    public function testGetQueuesSuccessful()
    {
        $this->handler->append(new Response(200, array(), '[{"name": "tasks", "messages": 2, "consumers": 1}]'));

        $queues = $this->management->getQueues();
        $this->assertInternalType('array', $queues);
        $this->assertCount(1, $queues);
        $this->assertInstanceOf(ManagementQueueResponse::class, $queues[0]);
    }

    public function testGetQueuesFailure()
    {
        $this->handler->append(new RequestException('Error Communicating with Server', new Request('GET', 'test')));

        $queues = $this->management->getQueues();
        $this->assertFalse($queues);
    }

    public function testGetQueueSuccessful()
    {
        $this->handler->append(new Response(200, array(), '{"name": "tasks", "messages": 3, "consumers": 1}'));

        $queue = $this->management->getQueue('tasks');
        $this->assertInstanceOf(ManagementQueueResponse::class, $queue);
        $this->assertSame('tasks', $queue->name);
        $this->assertSame(3, $queue->messages);
        $this->assertSame(1, $queue->consumers);
    }

    public function testGetQueueFailure()
    {
        $this->handler->append(new RequestException('Error Communicating with Server', new Request('GET', 'test')));

        $queue = $this->management->getQueue('tasks');
        $this->assertFalse($queue);
    }

    public function testShortestQueue()
    {
        $queues = array(
            array('name' => 'task1', 'messages' => 10, 'consumers' => 1),
            array('name' => 'task2', 'messages' => 2, 'consumers' => 1),
            array('name' => 'task3', 'messages' => 7, 'consumers' => 1),
            array('name' => 'task4', 'messages' => 5, 'consumers' => 1),
        );

        $this->handler->append(new RequestException('Error Communicating with Server', new Request('GET', 'test')));
        $this->handler->append(new Response(200, array(), json_encode($queues)));

        $this->assertFalse($this->management->getShortestQueueName());

        $queueName = $this->management->getShortestQueueName();
        $this->assertSame('task2', $queueName);
    }
}
