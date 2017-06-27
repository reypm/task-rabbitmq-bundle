<?php

/*
 * This file is part of the TaskRabbitMqBundle.
 *
 * (c) Yonel Ceruto <yonelceruto@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yceruto\TaskRabbitMqBundle\Consumer;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;
use Yceruto\TaskRabbitMqBundle\Model\Job;
use Yceruto\TaskRabbitMqBundle\Model\TaskManagerInterface;
use Yceruto\TaskRabbitMqBundle\Worker\WorkerContainer;

class TaskConsumer implements ConsumerInterface
{
    private $taskManager;
    private $workerContainer;
    private $logger;

    public function __construct(TaskManagerInterface $taskManager, WorkerContainer $workerContainer, LoggerInterface $logger = null)
    {
        $this->taskManager = $taskManager;
        $this->workerContainer = $workerContainer;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(AMQPMessage $msg)
    {
        $job = @unserialize($msg->body);

        if (!$job instanceof Job) {
            $this->log('error', 'Unknown job message. Reject message.');

            return ConsumerInterface::MSG_REJECT;
        }

        if (null === $task = $this->taskManager->findTaskById($job->getTaskId())) {
            $this->log('alert', 'Reject and drop message because the task does not exists.');

            return ConsumerInterface::MSG_REJECT;
        }

        if ($task->isStatusCancelled()) {
            $this->log('alert', 'Reject and drop message because the task was cancelled.');

            return ConsumerInterface::MSG_REJECT;
        }

        if ($task->isStatusPaused()) {
            $this->log('debug', 'Reject and requeue message because the task was paused.');

            return false;
        }

        $workerServiceId = $job->getWorkerServiceId();

        if (false === $this->workerContainer->has($workerServiceId)) {
            $this->taskManager->cancelTask($task);

            $this->log('error', sprintf('Reject and drop message because worker service "%s" not found.', $workerServiceId));

            return ConsumerInterface::MSG_REJECT;
        }

        $worker = $this->workerContainer->get($workerServiceId);

        try {
            if ($task->isStatusOnHold()) {
                $task->setStatusRunning();
                $this->taskManager->updateTask($task);
            }

            $result = $worker->execute($job->getData());
        } catch (\Exception $e) {
            $this->log('critical', $e->getMessage(), array('trace' => $e->getTrace()));

            $result = ConsumerInterface::MSG_REJECT;
            $this->taskManager->cancelTask($task);
        }

        if (null === $result || ConsumerInterface::MSG_ACK === $result) {
            $this->taskManager->updateTaskProgress($task, $job->getNumber());
        }

        return $result;
    }

    private function log($type, $message, array $context = array())
    {
        if (null === $this->logger) {
            return;
        }

        $this->logger->{$type}($message, $context);
    }
}
