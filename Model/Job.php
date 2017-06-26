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

final class Job
{
    /**
     * @var int
     */
    private $number;

    /**
     * @var mixed
     */
    private $taskId;

    /**
     * @var string
     */
    private $workerServiceId;

    /**
     * @var mixed
     */
    private $data;

    /**
     * Gets the job number.
     *
     * @return int
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Sets the job number.
     *
     * @param int $number
     *
     * @return Job
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Sets the job task id.
     *
     * @param mixed $taskId
     *
     * @return Job
     */
    public function setTaskId($taskId)
    {
        $this->taskId = $taskId;

        return $this;
    }

    /**
     * Gets the job task id.
     *
     * @return string
     */
    public function getTaskId()
    {
        return $this->taskId;
    }

    /**
     * Sets the job worker service id.
     *
     * @param string $workerServiceId
     *
     * @return Job
     */
    public function setWorkerServiceId($workerServiceId)
    {
        $this->workerServiceId = $workerServiceId;

        return $this;
    }

    /**
     * Gets the job worker service id.
     *
     * @return string|null
     */
    public function getWorkerServiceId()
    {
        return $this->workerServiceId;
    }

    /**
     * Sets the job data.
     *
     * @param mixed $data
     *
     * @return Job
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Gets the job data.
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }
}
