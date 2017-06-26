<?php

/*
 * This file is part of the TaskRabbitMqBundle.
 *
 * (c) Yonel Ceruto <yonelceruto@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yceruto\TaskRabbitMqBundle\Tests\Consumer;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Yceruto\TaskRabbitMqBundle\Consumer\TaskConsumer;
use Yceruto\TaskRabbitMqBundle\Model\Job;
use Yceruto\TaskRabbitMqBundle\Model\TaskManagerInterface;
use Yceruto\TaskRabbitMqBundle\Tests\TestTask;
use Yceruto\TaskRabbitMqBundle\Worker\WorkerContainer;
use Yceruto\TaskRabbitMqBundle\Worker\WorkerInterface;

class TaskConsumerTest extends TestCase
{
    /** @var TaskConsumer */
    private $consumer;
    /** @var TaskManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $taskManager;
    /** @var WorkerContainer|\PHPUnit_Framework_MockObject_MockObject */
    private $workerContainer;
    /** @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $logger;

    public function setUp()
    {
        $this->taskManager = $this->getMockForAbstractClass(TaskManagerInterface::class);
        $this->workerContainer = $this->getMockBuilder(WorkerContainer::class)->getMock();

        $this->logger = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->logger
            ->expects($this->any())
            ->method($this->anything())
        ;

        $this->consumer = new TaskConsumer($this->taskManager, $this->workerContainer, $this->logger);
    }

    public function testWithoutLogger()
    {
        $result = (new TaskConsumer($this->taskManager, $this->workerContainer, null))
            ->execute(new AMQPMessage(''));

        $this->assertSame(ConsumerInterface::MSG_REJECT, $result);
    }

    public function testDropBecauseUnknownJobMessage()
    {
        $msg = new AMQPMessage('');
        $result = $this->consumer->execute($msg);
        $this->assertSame(ConsumerInterface::MSG_REJECT, $result);
    }

    public function testDropBecauseMissingTask()
    {
        $msg = $this->createJobMessage(null);
        $result = $this->consumer->execute($msg);
        $this->assertSame(ConsumerInterface::MSG_REJECT, $result);
    }

    public function testDropBecauseTaskCancelled()
    {
        $task = new TestTask();
        $task->setId(1);
        $task->setStatusCancelled();

        $this->taskManager
            ->expects($this->once())
            ->method('findTaskById')
            ->with($this->equalTo($task->getId()))
            ->will($this->returnValue($task))
        ;

        $msg = $this->createJobMessage($task->getId());
        $result = $this->consumer->execute($msg);

        $this->assertSame(ConsumerInterface::MSG_REJECT, $result);
    }

    public function testRequeueBecauseTaskPaused()
    {
        $task = new TestTask();
        $task->setId(1);
        $task->setStatusPaused();

        $this->taskManager
            ->expects($this->once())
            ->method('findTaskById')
            ->with($this->equalTo($task->getId()))
            ->will($this->returnValue($task))
        ;

        $msg = $this->createJobMessage($task->getId());
        $result = $this->consumer->execute($msg);
        $this->assertFalse($result);
    }

    public function testDropBecauseWorkerNotFound()
    {
        $task = new TestTask();
        $task->setId(1);
        $task->setStatusOnHold();

        $this->taskManager
            ->expects($this->once())
            ->method('findTaskById')
            ->with($this->equalTo($task->getId()))
            ->will($this->returnValue($task))
        ;
        $this->taskManager
            ->expects($this->once())
            ->method('cancelTask')
            ->with($this->equalTo($task))
        ;
        $this->workerContainer
            ->expects($this->once())
            ->method('has')
            ->with($this->equalTo('wrong_worker_service_id'))
            ->will($this->returnValue(false))
        ;

        $msg = $this->createJobMessage($task->getId(), 'wrong_worker_service_id');
        $result = $this->consumer->execute($msg);
        $this->assertSame(ConsumerInterface::MSG_REJECT, $result);
    }

    public function testWorkerExecuteSuccessfully()
    {
        $task = new TestTask();
        $task->setId(1);
        $task->setStatusOnHold();

        $jobData = array('email' => 'john@gmail.com');

        $workerServiceId = 'app.worker.dummy';
        $worker = $this->getMockForAbstractClass(WorkerInterface::class);
        $worker
            ->expects($this->once())
            ->method('execute')
            ->with($jobData)
            ->will($this->returnValue(null))
        ;

        $this->taskManager
            ->expects($this->once())
            ->method('findTaskById')
            ->with($this->equalTo($task->getId()))
            ->will($this->returnValue($task))
        ;
        $this->workerContainer
            ->expects($this->once())
            ->method('has')
            ->with($this->equalTo($workerServiceId))
            ->will($this->returnValue(true))
        ;
        $this->workerContainer
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo($workerServiceId))
            ->will($this->returnValue($worker))
        ;
        $this->taskManager
            ->expects($this->once())
            ->method('updateTask')
            ->with($this->equalTo($task))
        ;
        $this->taskManager
            ->expects($this->once())
            ->method('updateTaskProgress')
            ->with($this->equalTo($task), $this->equalTo(1))
        ;

        $msg = $this->createJobMessage($task->getId(), $workerServiceId, $jobData);
        $result = $this->consumer->execute($msg);
        $this->assertNull($result);
    }

    public function testWorkerExecuteFailure()
    {
        $task = new TestTask();
        $task->setId(1);
        $task->setStatusOnHold();

        $jobData = array('email' => 'john@gmail.com');

        $workerServiceId = 'app.worker.dummy';
        $worker = $this->getMockForAbstractClass(WorkerInterface::class);
        $worker
            ->expects($this->once())
            ->method('execute')
            ->with($jobData)
            ->will($this->throwException(new \RuntimeException('Internal Error')))
        ;

        $this->taskManager
            ->expects($this->once())
            ->method('findTaskById')
            ->with($this->equalTo($task->getId()))
            ->will($this->returnValue($task))
        ;
        $this->workerContainer
            ->expects($this->once())
            ->method('has')
            ->with($this->equalTo($workerServiceId))
            ->will($this->returnValue(true))
        ;
        $this->workerContainer
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo($workerServiceId))
            ->will($this->returnValue($worker))
        ;
        $this->taskManager
            ->expects($this->once())
            ->method('updateTask')
            ->with($this->equalTo($task))
        ;
        $this->taskManager
            ->expects($this->once())
            ->method('cancelTask')
            ->with($this->equalTo($task))
        ;

        $msg = $this->createJobMessage($task->getId(), $workerServiceId, $jobData);
        $result = $this->consumer->execute($msg);
        $this->assertSame(ConsumerInterface::MSG_REJECT, $result);
    }

    protected function createJobMessage($taskId, $workerServiceId = null, $data = null)
    {
        $job = (new Job())
            ->setNumber(1)
            ->setTaskId($taskId)
            ->setWorkerServiceId($workerServiceId)
            ->setData($data)
        ;

        return new AMQPMessage(serialize($job));
    }
}
