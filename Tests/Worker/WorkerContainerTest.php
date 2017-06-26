<?php

/*
 * This file is part of the TaskRabbitMqBundle.
 *
 * (c) Yonel Ceruto <yonelceruto@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yceruto\TaskRabbitMqBundle\Tests\Worker;

use PHPUnit\Framework\TestCase;
use Yceruto\TaskRabbitMqBundle\Worker\WorkerContainer;
use Yceruto\TaskRabbitMqBundle\Worker\WorkerInterface;

class WorkerContainerTest extends TestCase
{
    public function testAddHasGet()
    {
        $container = $this->getWorkerContainer();

        $id = 'app.worker.send_email';
        $worker = $this->getWorker();

        $container->add($id, $worker);
        $this->assertTrue($container->has($id));
        $this->assertSame($worker, $container->get($id));
    }

    /**
     * @expectedException \Yceruto\TaskRabbitMqBundle\Exception\WorkerNotFoundException
     */
    public function testGetFailure()
    {
        $container = $this->getWorkerContainer();

        $container->get('unknown');
    }

    public function testFindServiceIdSuccessful()
    {
        $container = $this->getWorkerContainer();

        $id = 'app.worker.send_email';
        $worker = $this->getWorker();

        $container->add($id, $worker);
        $this->assertSame($id, $container->findServiceId($worker));
    }

    /**
     * @expectedException \Yceruto\TaskRabbitMqBundle\Exception\WorkerNotFoundException
     */
    public function testFailureFindServiceId()
    {
        $container = $this->getWorkerContainer();

        $container->findServiceId($this->getWorker());
    }

    /**
     * @return WorkerContainer
     */
    protected function getWorkerContainer()
    {
        return $this->getMockForAbstractClass(WorkerContainer::class);
    }

    /**
     * @return WorkerInterface
     */
    protected function getWorker()
    {
        return $this->getMockForAbstractClass(WorkerInterface::class);
    }
}
