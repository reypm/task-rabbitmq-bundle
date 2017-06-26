<?php

/*
 * This file is part of the TaskRabbitMqBundle.
 *
 * (c) Yonel Ceruto <yonelceruto@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yceruto\TaskRabbitMqBundle\Tests\Doctrine;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;
use Yceruto\TaskRabbitMqBundle\Doctrine\TaskManager;
use Yceruto\TaskRabbitMqBundle\Tests\TestTask;

class TaskManagerTest extends TestCase
{
    const TASK_CLASS = TestTask::class;

    /** @var TaskManager */
    private $taskManager;
    /** @var ObjectManager|\PHPUnit_Framework_MockObject_MockObject */
    private $om;
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $repository;

    public function setUp()
    {
        $class = $this->getMockBuilder(ClassMetadata::class)->getMock();
        $this->om = $this->getMockBuilder(ObjectManager::class)->getMock();
        $this->repository = $this->getMockBuilder(ObjectRepository::class)->getMock();

        $this->om
            ->expects($this->any())
            ->method('getRepository')
            ->with($this->equalTo(self::TASK_CLASS))
            ->will($this->returnValue($this->repository))
        ;
        $this->om
            ->expects($this->any())
            ->method('getClassMetadata')
            ->with($this->equalTo(self::TASK_CLASS))
            ->will($this->returnValue($class))
        ;
        $class
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(self::TASK_CLASS))
        ;

        $this->taskManager = new TaskManager($this->om, self::TASK_CLASS);
    }

    public function testGetClass()
    {
        $this->assertSame(self::TASK_CLASS, $this->taskManager->getClass());
    }

    public function testCreateTask()
    {
        $task = $this->taskManager->createTask();

        $this->assertInstanceOf(self::TASK_CLASS, $task);
        $this->assertNull($task->getStatus());
    }

    public function testDeleteTask()
    {
        $task = $this->getTask();

        $this->om
            ->expects($this->once())
            ->method('remove')
            ->with($this->equalTo($task))
        ;
        $this->om
            ->expects($this->once())
            ->method('flush')
        ;

        $this->taskManager->deleteTask($task);
    }

    public function testCancelTask()
    {
        $task = $this->getTask();

        $this->om
            ->expects($this->once())
            ->method('merge')
            ->with($this->equalTo($task))
            ->will($this->returnValue($task))
        ;
        $this->om
            ->expects($this->once())
            ->method('flush')
        ;

        $this->taskManager->cancelTask($task);
        $this->assertTrue($task->isStatusCancelled());
    }

    public function testUpdateTask()
    {
        $task = $this->getTask();

        $this->om
            ->expects($this->once())
            ->method('persist')
            ->with($this->equalTo($task))
        ;
        $this->om
            ->expects($this->once())
            ->method('flush')
        ;

        $this->taskManager->updateTask($task);
    }

    public function testUpdateTaskProgress()
    {
        $task = $this->getTask();
        $task->addJobData(array('email' => 'john@gmail.com'));
        $task->addJobData(array('email' => 'jane@gmail.com'));
        $task->setStatusOnHold();

        $this->om
            ->expects($this->exactly(2))
            ->method('merge')
            ->with($this->equalTo($task))
            ->will($this->returnValue($task))
        ;
        $this->om
            ->expects($this->exactly(2))
            ->method('flush')
        ;

        $this->taskManager->updateTaskProgress($task, 1);
        $this->assertSame(1, $task->getProcessedJobsCount());
        $this->assertTrue($task->isStatusRunning());

        $this->taskManager->updateTaskProgress($task, 2);
        $this->assertSame(2, $task->getProcessedJobsCount());
        $this->assertTrue($task->isStatusCompleted());
    }

    public function testFindTaskBy()
    {
        $criteria = array('foo' => 'bar');
        $this->repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo($criteria))
            ->will($this->returnValue(array()))
        ;

        $this->taskManager->findTaskBy($criteria);
    }

    public function testFindTaskById()
    {
        $this->repository
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo(1))
            ->will($this->returnValue(null))
        ;

        $this->taskManager->findTaskById(1);
    }

    public function testFindTasks()
    {
        $this->repository
            ->expects($this->once())
            ->method('findAll')
            ->will($this->returnValue(array()))
        ;

        $this->taskManager->findTasks();
    }

    /**
     * @return TestTask
     */
    protected function getTask()
    {
        $taskClass = self::TASK_CLASS;

        return new $taskClass();
    }
}
