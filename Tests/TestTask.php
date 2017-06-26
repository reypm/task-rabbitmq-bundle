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

use Yceruto\TaskRabbitMqBundle\Model\Task;

class TestTask extends Task
{
    public function setId($id)
    {
        $this->id = $id;
    }
}
