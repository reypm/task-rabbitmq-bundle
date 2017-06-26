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

interface WorkerAwareInterface
{
    /**
     * Gets the action worker service id.
     *
     * @return string
     */
    public function getWorkerServiceId();
}
