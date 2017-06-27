<?php

/*
 * This file is part of the TaskRabbitMqBundle.
 *
 * (c) Yonel Ceruto <yonelceruto@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yceruto\TaskRabbitMqBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Experimental!
 */
class LoadBalancerCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('task_rabbit_mq:load:balancer')
            ->setDescription('For load balancer daemon.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $phpFinder = new PhpExecutableFinder();
        $php = $phpFinder->find(false);
        $phpArguments = $phpFinder->findArguments();

        $console = $this->getContainer()->getParameter('kernel.root_dir').'/../bin/console';

        $delay = $this->getContainer()->getParameter('task_rabbit_mq.load_balancer.delay');
        $consumerType = $this->getContainer()->getParameter('task_rabbit_mq.load_balancer.consumer_type');
        $consumerName = $this->getContainer()->getParameter('task_rabbit_mq.load_balancer.consumer_name');

        if (null === $consumerType || null === $consumerName) {
            throw new \RuntimeException('Configure "consumer_type" and "consumer_name" parameters from "load_balancer" under "task_rabbit_mq" configuration.');
        }

        while (true) {
            sleep($delay);

            // check consumer balance
            $process = new Process(array_merge(array($php), $phpArguments, array($console, 'task_rabbit_mq:consumer:balance')));
            $process->run();
            $balance = (int) $process->getOutput();

            while ($balance < 0) {
                // we need more consumers
                $command = sprintf('%s %s rabbitmq:%s %s -w', $php, $console, $consumerType, $consumerName);
                $process = new Process($command);
                $process->start();

                ++$balance;
            }
        }
    }
}
