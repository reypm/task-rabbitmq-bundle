<?php

/*
 * This file is part of the TaskRabbitMqBundle.
 *
 * (c) Yonel Ceruto <yonelceruto@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yceruto\TaskRabbitMqBundle\Doctrine;

use Doctrine\Common\Persistence\ObjectManager;
use Yceruto\TaskRabbitMqBundle\Model\TaskInterface;
use Yceruto\TaskRabbitMqBundle\Model\TaskManagerInterface;

class TaskManager implements TaskManagerInterface
{
    private $objectManager;
    private $repository;
    private $class;

    public function __construct(ObjectManager $objectManager, $class)
    {
        $this->objectManager = $objectManager;
        $this->repository = $objectManager->getRepository($class);

        // support to short entity syntax e.g. AppBundle:Task
        $metadata = $objectManager->getClassMetadata($class);
        $this->class = $metadata->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * {@inheritdoc}
     */
    public function createTask()
    {
        $class = $this->getClass();

        /** @var TaskInterface $task */
        $task = new $class();

        return $task;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteTask(TaskInterface $task)
    {
        $this->objectManager->remove($task);
        $this->objectManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function updateTask(TaskInterface $task)
    {
        if (null === $task->getId()) {
            if (0 === $task->getJobsCount()) {
                // avoid create task without jobs
                // allowing to execute the task at least one time
                $task->addJobData(null);
            }

            $this->objectManager->persist($task);
        }

        $this->objectManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function cancelTask(TaskInterface $task)
    {
        /** @var TaskInterface $task */
        $task = $this->objectManager->merge($task);

        $task->setStatusCancelled();

        $this->objectManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function updateTaskProgress(TaskInterface $task, $count)
    {
        /** @var TaskInterface $task */
        $task = $this->objectManager->merge($task);

        $totalJobs = $task->getJobsCount();

        // avoid more that 100%
        if ($count < $totalJobs) {
            // decrement is no allowed
            if ($count > $task->getProcessedJobsCount()) {
                $task->setProcessedJobsCount($count);
            }
        } else {
            $task->setProcessedJobsCount($totalJobs);
        }

        if ($task->getProcessedJobsCount() === $totalJobs) {
            $task->setStatusCompleted();
        } elseif ($task->isStatusOnHold()) {
            $task->setStatusRunning();
        }

        $this->objectManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function findTasks()
    {
        return $this->repository->findAll();
    }

    /**
     * {@inheritdoc}
     */
    public function findTaskBy(array $criteria)
    {
        return $this->repository->findOneBy($criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function findTaskById($id)
    {
        return $this->repository->find($id);
    }
}
