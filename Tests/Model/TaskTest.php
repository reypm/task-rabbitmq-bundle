<?php

/*
 * This file is part of the TaskRabbitMqBundle.
 *
 * (c) Yonel Ceruto <yonelceruto@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yceruto\TaskRabbitMqBundle\Tests\Model;

use PHPUnit\Framework\TestCase;
use Yceruto\TaskRabbitMqBundle\Model\Task;
use Yceruto\TaskRabbitMqBundle\Model\TaskInterface;

class TaskTest extends TestCase
{
    public function testName()
    {
        $task = $this->getTask();
        $this->assertNull($task->getName());

        $task->setName('Send Email');
        $this->assertSame('Send Email', $task->getName());
    }

    public function testStatus()
    {
        $task = $this->getTask();
        $this->assertSame(TaskInterface::ON_HOLD, $task->getStatus());
        $this->assertTrue($task->isStatusOnHold());

        $task->setStatusRunning();
        $this->assertSame(TaskInterface::RUNNING, $task->getStatus());
        $this->assertTrue($task->isStatusRunning());

        $task->setStatusPaused();
        $this->assertSame(TaskInterface::PAUSED, $task->getStatus());
        $this->assertTrue($task->isStatusPaused());

        $task->setStatusCompleted();
        $this->assertSame(TaskInterface::COMPLETED, $task->getStatus());
        $this->assertTrue($task->isStatusCompleted());

        $task->setStatusCancelled();
        $this->assertSame(TaskInterface::CANCELLED, $task->getStatus());
        $this->assertTrue($task->isStatusCancelled());
    }

    public function testJobsData()
    {
        $task = $this->getTask();
        $this->assertNull($task->getJobsData());

        $task->setJobsData(array(array('email' => 'john@gmail.com')));

        $task->addJobData(array('email' => 'jane@gmail.com'));
        $this->assertSame(array(array('email' => 'john@gmail.com'), array('email' => 'jane@gmail.com')), $task->getJobsData());
    }

    public function testJobsCount()
    {
        $task = $this->getTask();
        $this->assertSame(0, $task->getJobsCount());

        $task->addJobData(array('email' => 'john@gmail.com'));
        $this->assertSame(1, $task->getJobsCount());
    }

    public function testProcessedJobsCount()
    {
        $task = $this->getTask();
        $this->assertSame(0, $task->getProcessedJobsCount());

        $task->setProcessedJobsCount(1);
        $this->assertSame(1, $task->getProcessedJobsCount());
    }

    public function testProgress()
    {
        $task = $this->getTask();
        $this->assertSame(0, $task->getProgress());

        $task->setJobsData(array(
            array('email' => 'john@gmail.com'),
            array('email' => 'jane@gmail.com'),
            array('email' => 'anna@gmail.com'),
        ));

        $task->setProcessedJobsCount(1);
        $this->assertSame(33, (int) $task->getProgress());

        $task->setProcessedJobsCount(2);
        $this->assertSame(66, (int) $task->getProgress());

        $task->setProcessedJobsCount(3);
        $this->assertSame(100, (int) $task->getProgress());
    }

    /**
     * @return Task
     */
    protected function getTask()
    {
        return $this->getMockForAbstractClass(Task::class);
    }
}
