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
use Yceruto\TaskRabbitMqBundle\Model\Job;

class JobTest extends TestCase
{
    public function testNumber()
    {
        $job = new Job();
        $this->assertNull($job->getNumber());

        $job->setNumber(1);
        $this->assertSame(1, $job->getNumber());
    }

    public function testTaskId()
    {
        $job = new Job();
        $this->assertNull($job->getTaskId());

        $job->setTaskId(1);
        $this->assertSame(1, $job->getTaskId());
    }

    public function testWorkerServiceId()
    {
        $job = new Job();
        $this->assertNull($job->getWorkerServiceId());

        $job->setWorkerServiceId('app.worker.custom_send_email');
        $this->assertSame('app.worker.custom_send_email', $job->getWorkerServiceId());
    }

    public function testData()
    {
        $job = new Job();
        $this->assertNull($job->getData());

        $job->setData(array('email' => 'john@gmail.com'));
        $this->assertSame(array('email' => 'john@gmail.com'), $job->getData());
    }
}
