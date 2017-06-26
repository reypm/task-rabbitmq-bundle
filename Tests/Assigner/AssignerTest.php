<?php

/*
 * This file is part of the TaskRabbitMqBundle.
 *
 * (c) Yonel Ceruto <yonelceruto@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yceruto\TaskRabbitMqBundle\Tests\Assigner;

use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use PHPUnit\Framework\TestCase;
use Yceruto\TaskRabbitMqBundle\Assigner\TaskAssigner;
use Yceruto\TaskRabbitMqBundle\RabbitMq\Management\Management;
use Yceruto\TaskRabbitMqBundle\Tests\TestTask;
use Yceruto\TaskRabbitMqBundle\Tests\TestWorker;
use Yceruto\TaskRabbitMqBundle\Worker\WorkerAwareInterface;
use Yceruto\TaskRabbitMqBundle\Worker\WorkerContainer;

class AssignerTest extends TestCase
{
    /** @var TaskAssigner */
    private $assigner;
    /** @var WorkerContainer|\PHPUnit_Framework_MockObject_MockObject */
    private $workerContainer;
    /** @var Management|\PHPUnit_Framework_MockObject_MockObject */
    private $management;
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $producer;

    public function setUp()
    {
        $this->workerContainer = $this->getMockBuilder(WorkerContainer::class)->getMock();
        $this->management = $this->getMockBuilder(Management::class)->disableOriginalConstructor()->getMock();
        $this->producer = $this->getMockForAbstractClass(ProducerInterface::class);

        $this->assigner = new TaskAssigner($this->workerContainer, $this->management, $this->producer);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Expected persisted task. Please, save it before assigning to the worker.
     */
    public function testExpectedValidTaskArgument()
    {
        $task = new TestTask();
        $this->assigner->assign($task, TestWorker::class);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Expected at least one job to execute.
     */
    public function testExpectedAtLeastOneJob()
    {
        $task = new TestTask();
        $task->setId(1);

        $this->assigner->assign($task, TestWorker::class);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Expected a Worker instance or its service id.
     */
    public function testExpectedValidWorkerArgument()
    {
        $task = new TestTask();
        $task->setId(1);
        $task->addJobData(array('email' => 'john@gmail.com'));

        $this->assigner->assign($task, 'unknown');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Queue not found. Make sure to configure at least one queue.
     */
    public function testQueueNotFoundException()
    {
        $this->workerContainer
            ->expects($this->once())
            ->method('has')
            ->with($this->equalTo(TestWorker::class))
            ->will($this->returnValue(true))
        ;
        $this->management
            ->expects($this->once())
            ->method('getShortestQueueName')
            ->will($this->returnValue(false))
        ;

        $task = new TestTask();
        $task->setId(1);
        $task->addJobData(array('email' => 'john@gmail.com'));

        $this->assigner->assign($task, TestWorker::class);
    }

    public function testAssignNormal()
    {
        $this->workerContainer
            ->expects($this->once())
            ->method('has')
            ->with($this->equalTo(TestWorker::class))
            ->will($this->returnValue(true))
        ;
        $this->management
            ->expects($this->once())
            ->method('getShortestQueueName')
            ->will($this->returnValue('tasks'))
        ;
        $this->producer
            ->expects($this->exactly(2))
            ->method('publish')
            ->withAnyParameters()
        ;

        $task = new TestTask();
        $task->setId(1);
        $task->addJobData(array('email' => 'john@gmail.com'));
        $task->addJobData(array('email' => 'jane@gmail.com'));

        $this->assigner->assign($task, TestWorker::class);
    }

    public function testAssignCustomWorkerFromOneJob()
    {
        $this->workerContainer
            ->expects($this->exactly(2))
            ->method('has')
            ->withAnyParameters()
            ->will($this->returnValue(true))
        ;
        $this->management
            ->expects($this->once())
            ->method('getShortestQueueName')
            ->will($this->returnValue('tasks'))
        ;
        $this->producer
            ->expects($this->exactly(2))
            ->method('publish')
            ->withAnyParameters()
        ;

        $task = new TestTask();
        $task->setId(1);
        $task->addJobData(array('email' => 'john@gmail.com'));
        $task->addJobData(new DummySendEmailJob());

        $this->assigner->assign($task, TestWorker::class);
    }

    public function testAssignPassingWorkerInstance()
    {
        $worker = new TestWorker();

        $this->workerContainer
            ->expects($this->once())
            ->method('findServiceId')
            ->with($this->equalTo($worker))
            ->will($this->returnValue(TestWorker::class))
        ;
        $this->management
            ->expects($this->once())
            ->method('getShortestQueueName')
            ->will($this->returnValue('tasks'))
        ;
        $this->producer
            ->expects($this->once())
            ->method('publish')
            ->withAnyParameters()
        ;

        $task = new TestTask();
        $task->setId(1);
        $task->addJobData(array('email' => 'john@gmail.com'));

        $this->assigner->assign($task, $worker);
    }
}

class DummySendEmailJob implements WorkerAwareInterface
{
    /**
     * {@inheritdoc}
     */
    public function getWorkerServiceId()
    {
        return 'app.worker.special_send_email';
    }
}
