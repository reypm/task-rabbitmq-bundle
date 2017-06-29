<?php

/*
 * This file is part of the TaskRabbitMqBundle.
 *
 * (c) Yonel Ceruto <yonelceruto@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yceruto\TaskRabbitMqBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Yceruto\TaskRabbitMqBundle\Model\TaskInterface;

class TaskController extends Controller
{
    public function indexAction()
    {
        $taskManager = $this->get('task_rabbit_mq.manager');

        return $this->json(array('tasks' => $taskManager->findTasks()));
    }

    public function pauseAction($id)
    {
        $taskManager = $this->get('task_rabbit_mq.manager');
        /** @var TaskInterface $task */
        $task = $taskManager->findTaskById($id);

        if ($task->isStatusPaused()) {
            $task->setStatusRunning();
        } elseif ($task->isStatusRunning()) {
            $task->setStatusPaused();
        }

        $taskManager->updateTask($task);

        return $this->indexAction();
    }

    public function cancelAction($id)
    {
        $taskManager = $this->get('task_rabbit_mq.manager');
        $task = $taskManager->findTaskById($id);

        $taskManager->cancelTask($task, 'Manually');

        return $this->indexAction();
    }

    public function deleteAction($id)
    {
        $taskManager = $this->get('task_rabbit_mq.manager');
        /** @var TaskInterface $task */
        $task = $taskManager->findTaskById($id);

        $taskManager->deleteTask($task);

        return $this->indexAction();
    }
}
