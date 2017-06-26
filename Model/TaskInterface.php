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

interface TaskInterface
{
    const ON_HOLD = 0;   // Waiting for a consumer.
    const RUNNING = 1;   // Processing task.
    const PAUSED = 2;    // Paused manually.
    const COMPLETED = 3; // All jobs has been executed.
    const CANCELLED = 4; // Cancelled manually.

    /**
     * Returns the task unique id.
     *
     * @return mixed
     */
    public function getId();

    /**
     * Sets the task name.
     *
     * @param string $name
     *
     * @return TaskInterface
     */
    public function setName($name);

    /**
     * Gets the task name.
     *
     * @return string
     */
    public function getName();

    /**
     * Sets on hold task status.
     *
     * @return TaskInterface
     */
    public function setStatusOnHold();

    /**
     * Sets running task status.
     *
     * @return TaskInterface
     */
    public function setStatusRunning();

    /**
     * Sets paused task status.
     *
     * @return TaskInterface
     */
    public function setStatusPaused();

    /**
     * Sets completed task status.
     *
     * @return TaskInterface
     */
    public function setStatusCompleted();

    /**
     * Sets cancelled task status.
     *
     * @return TaskInterface
     */
    public function setStatusCancelled();

    /**
     * Is on hold task status.
     *
     * @return bool
     */
    public function isStatusOnHold();

    /**
     * Is running task status.
     *
     * @return bool
     */
    public function isStatusRunning();

    /**
     * Is paused task status.
     *
     * @return bool
     */
    public function isStatusPaused();

    /**
     * Is completed task status.
     *
     * @return bool
     */
    public function isStatusCompleted();

    /**
     * Is cancelled task.
     *
     * @return bool
     */
    public function isStatusCancelled();

    /**
     * Gets the task status.
     *
     * @return string
     */
    public function getStatus();

    /**
     * Sets the task jobs data.
     *
     * @param array $jobs
     *
     * @return TaskInterface
     */
    public function setJobsData(array $jobs);

    /**
     * Add a task job data.
     *
     * @param mixed $data
     *
     * @return TaskInterface
     */
    public function addJobData($data);

    /**
     * Gets the task jobs data.
     *
     * @return array
     */
    public function getJobsData();

    /**
     * Gets the count of jobs.
     *
     * @return int
     */
    public function getJobsCount();

    /**
     * Increment the total jobs processed.
     *
     * @param int $processedCount
     *
     * @return TaskInterface
     */
    public function setProcessedJobsCount($processedCount);

    /**
     * Gets the total jobs processed.
     *
     * @return int
     */
    public function getProcessedJobsCount();
}
