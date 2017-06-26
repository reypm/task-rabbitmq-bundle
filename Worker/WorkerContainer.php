<?php

/*
 * This file is part of the TaskRabbitMqBundle.
 *
 * (c) Yonel Ceruto <yonelceruto@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yceruto\TaskRabbitMqBundle\Worker;

use Psr\Container\ContainerInterface;
use Yceruto\TaskRabbitMqBundle\Exception\WorkerNotFoundException;

class WorkerContainer implements ContainerInterface
{
    /**
     * @var WorkerInterface[]
     */
    private $workers = array();

    /**
     * Add a worker.
     *
     * @param string          $id
     * @param WorkerInterface $worker
     *
     * @return WorkerContainer
     */
    public function add($id, WorkerInterface $worker)
    {
        $this->workers[$id] = $worker;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        if (!$this->has($id)) {
            throw new WorkerNotFoundException($id);
        }

        return $this->workers[$id];
    }

    /**
     * {@inheritdoc}
     */
    public function has($id)
    {
        return isset($this->workers[$id]);
    }

    /**
     * Gets the worker service name.
     *
     * @param WorkerInterface $worker
     *
     * @return string|bool
     */
    public function findServiceId(WorkerInterface $worker)
    {
        if (false === $id = array_search($worker, $this->workers, true)) {
            throw new WorkerNotFoundException($worker);
        }

        return $id;
    }
}
