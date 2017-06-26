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

interface WorkerInterface
{
    /**
     * Process the job data.
     *
     * @param object|array $data The job data
     *
     * @return mixed false to reject and requeue action, any other value to acknowledge
     */
    public function execute($data);
}
