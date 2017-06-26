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
 * Storage agnostic task object.
 */
abstract class Task implements TaskInterface
{
    /**
     * @var mixed
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var int
     */
    protected $status;

    /**
     * @var int
     */
    protected $jobsCount;

    /**
     * @var int
     */
    protected $jobsProcessedCount;

    /**
     * Only for creation purpose.
     *
     * @var array
     */
    private $jobs;

    public function __construct()
    {
        $this->jobsCount = 0;
        $this->jobsProcessedCount = 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setStatusOnHold()
    {
        $this->status = self::ON_HOLD;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setStatusRunning()
    {
        $this->status = self::RUNNING;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setStatusPaused()
    {
        $this->status = self::PAUSED;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setStatusCompleted()
    {
        $this->status = self::COMPLETED;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setStatusCancelled()
    {
        $this->status = self::CANCELLED;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isStatusOnHold()
    {
        return self::ON_HOLD === $this->status;
    }

    /**
     * {@inheritdoc}
     */
    public function isStatusRunning()
    {
        return self::RUNNING === $this->status;
    }

    /**
     * {@inheritdoc}
     */
    public function isStatusPaused()
    {
        return self::PAUSED === $this->status;
    }

    /**
     * {@inheritdoc}
     */
    public function isStatusCompleted()
    {
        return self::COMPLETED === $this->status;
    }

    /**
     * {@inheritdoc}
     */
    public function isStatusCancelled()
    {
        return self::CANCELLED === $this->status;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * {@inheritdoc}
     */
    public function setJobsData(array $jobs)
    {
        $this->jobs = $jobs;
        $this->jobsCount = count($jobs);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addJobData($data)
    {
        $this->jobs[] = $data;
        ++$this->jobsCount;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getJobsData()
    {
        return $this->jobs;
    }

    /**
     * {@inheritdoc}
     */
    public function getJobsCount()
    {
        return $this->jobsCount;
    }

    /**
     * {@inheritdoc}
     */
    public function setProcessedJobsCount($processedCount)
    {
        $this->jobsProcessedCount = $processedCount;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getProcessedJobsCount()
    {
        return $this->jobsProcessedCount;
    }

    /**
     * Gets the current task progress.
     *
     * @return float
     */
    public function getProgress()
    {
        return $this->jobsCount > 0
            ? ($this->jobsProcessedCount / $this->jobsCount) * 100
            : 0;
    }
}
