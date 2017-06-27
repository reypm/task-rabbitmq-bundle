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

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Yceruto\TaskRabbitMqBundle\DependencyInjection\Compiler\AddWorkerCompilerPass;

class TaskRabbitMqBundle extends Bundle
{
    const VERSION = '1.0-DEV';

    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new AddWorkerCompilerPass());
        $mappings = array(
            realpath(__DIR__.'/Resources/config/doctrine-mapping') => 'Yceruto\TaskRabbitMqBundle\Model',
        );
        if (class_exists('Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass')) {
            $container->addCompilerPass(DoctrineOrmMappingsPass::createXmlMappingDriver($mappings, array('task_rabbit_mq.model_manager_name')));
        }
    }
}
