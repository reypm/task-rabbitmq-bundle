<?php

/*
 * This file is part of the TaskRabbitMqBundle.
 *
 * (c) Yonel Ceruto <yonelceruto@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yceruto\TaskRabbitMqBundle\Assigner;

use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Yceruto\TaskRabbitMqBundle\Model\Job;
use Yceruto\TaskRabbitMqBundle\Model\TaskInterface;
use Yceruto\TaskRabbitMqBundle\RabbitMq\Management\Management;
use Yceruto\TaskRabbitMqBundle\Worker\WorkerAwareInterface;
use Yceruto\TaskRabbitMqBundle\Worker\WorkerContainer;
use Yceruto\TaskRabbitMqBundle\Worker\WorkerInterface;

class TaskAssigner
{
    private $workerContainer;
    private $management;
    private $producer;

    public function __construct(WorkerContainer $workerContainer, Management $management, ProducerInterface $producer)
    {
        $this->workerContainer = $workerContainer;
        $this->management = $management;
        $this->producer = $producer;
    }

    /**
     * Assigns a task to the worker.
     *
     * @param TaskInterface          $task
     * @param WorkerInterface|string $worker The worker instance or FQCN
     */
    public function assign(TaskInterface $task, $worker)
    {
        if (null === $task->getId()) {
            throw new \InvalidArgumentException('Expected persisted task. Please, save it before assigning to the worker.');
        }

        if (0 === $task->getJobsCount()) {
            throw new \InvalidArgumentException('Expected at least one job to execute.');
        }

        if ($worker instanceof WorkerInterface) {
            $defaultWorkerServiceId = $this->workerContainer->findServiceId($worker);
        } elseif (is_string($worker) && $this->workerContainer->has($worker)) {
            $defaultWorkerServiceId = $worker;
        } else {
            throw new \InvalidArgumentException('Expected a Worker instance or its service id.');
        }

        if (false === $queue = $this->management->getShortestQueueName()) {
            throw new \InvalidArgumentException('Queue not found. Make sure to configure at least one queue.');
        }

        $i = 0;
        foreach ($task->getJobsData() as $data) {
            $job = new Job();
            $job->setNumber(++$i);
            $job->setTaskId($task->getId());
            $job->setData($data);

            if ($data instanceof WorkerAwareInterface && $this->workerContainer->has($data->getWorkerServiceId())) {
                $job->setWorkerServiceId($data->getWorkerServiceId());
            } else {
                $job->setWorkerServiceId($defaultWorkerServiceId);
            }

            // In this case the routing_key === queue name (by configuration)
            $this->producer->publish(serialize($job), $queue);
        }
    }
}
