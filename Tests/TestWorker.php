<?php

/*
 * This file is part of the TaskRabbitMqBundle.
 *
 * (c) Yonel Ceruto <yonelceruto@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yceruto\TaskRabbitMqBundle\Tests;

use Yceruto\TaskRabbitMqBundle\Worker\WorkerInterface;

class TestWorker implements WorkerInterface
{
    public function execute($data)
    {
    }
}
