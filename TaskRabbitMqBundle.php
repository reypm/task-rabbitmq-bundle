<?php

/*
 * This file is part of the TaskRabbitMqBundle.
 *
 * (c) Yonel Ceruto <yonelceruto@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yceruto\TaskRabbitMqBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Yceruto\TaskRabbitMqBundle\DependencyInjection\Compiler\AddWorkerCompilerPass;

class TaskRabbitMqBundle extends Bundle
{
    const VERSION = '1.0-DEV';

    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new AddWorkerCompilerPass());
    }
}
