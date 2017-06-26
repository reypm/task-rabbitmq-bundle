<?php

/*
 * This file is part of the TaskRabbitMqBundle.
 *
 * (c) Yonel Ceruto <yonelceruto@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yceruto\TaskRabbitMqBundle\Model;

/**
 * Interface to be implemented by task managers. This adds an additional level
 * of abstraction between your application, and the actual repository.
 *
 * All changes to tasks should happen through this interface.
 */
interface TaskManagerInterface
{
    /**
     * Returns the task's fully qualified class name.
     *
     * @return string
     */
    public function getClass();

    /**
     * Creates an empty task instance.
     *
     * @return TaskInterface
     */
    public function createTask();

    /**
     * Deletes a task.
     *
     * @param TaskInterface $task
     */
    public function deleteTask(TaskInterface $task);

    /**
     * Updates a task.
     *
     * @param TaskInterface $task
     */
    public function updateTask(TaskInterface $task);

    /**
     * Cancel a task.
     *
     * @param TaskInterface $task
     */
    public function cancelTask(TaskInterface $task);

    /**
     * Updates the task progress.
     *
     * @param TaskInterface $task
     * @param int           $count
     */
    public function updateTaskProgress(TaskInterface $task, $count);

    /**
     * Returns a collection with all task instances.
     *
     * @return TaskInterface[].
     */
    public function findTasks();

    /**
     * Finds one task by the given criteria.
     *
     * @param array $criteria
     *
     * @return TaskInterface
     */
    public function findTaskBy(array $criteria);

    /**
     * Finds one task by the given id.
     *
     * @param mixed $id
     *
     * @return TaskInterface
     */
    public function findTaskById($id);
}
