<?php

/*
 * This file is part of the TaskRabbitMqBundle.
 *
 * (c) Yonel Ceruto <yonelceruto@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yceruto\TaskRabbitMqBundle\Exception;

use Psr\Container\NotFoundExceptionInterface;
use Yceruto\TaskRabbitMqBundle\Worker\WorkerInterface;

class WorkerNotFoundException extends \RuntimeException implements NotFoundExceptionInterface
{
    /**
     * @param WorkerInterface|string $worker
     */
    public function __construct($worker)
    {
        if ($worker instanceof WorkerInterface) {
            $worker = get_class($worker);
        }

        parent::__construct(sprintf('Task worker "%s" not found.', $worker));
    }
}
