<?php

/*
 * This file is part of the TaskRabbitMqBundle.
 *
 * (c) Yonel Ceruto <yonelceruto@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yceruto\TaskRabbitMqBundle\DataCollector;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Yceruto\TaskRabbitMqBundle\Model\TaskManagerInterface;

class TaskCollector extends DataCollector
{
    private $taskManager;

    public function __construct(TaskManagerInterface $taskManager)
    {
        $this->taskManager = $taskManager;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $tasks = $this->taskManager->findTasks();

        $total = 0;
        $processed = 0;
        foreach ($tasks as $task) {
            $total += $task->getJobsCount();
            $processed += $task->getProcessedJobsCount();
        }

        $this->data['tasks'] = $tasks;
        $this->data['progress'] = $total > 0 ? $processed / $total * 100 : 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'task_rabbit_mq';
    }

    public function getTasks()
    {
        return $this->data['tasks'];
    }

    public function getProgress()
    {
        return $this->data['progress'];
    }
}
