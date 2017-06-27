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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ConsumerBalanceCommand extends ContainerAwareCommand
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('task_rabbit_mq:consumer:balance')
            ->addOption('per', 'p', InputOption::VALUE_OPTIONAL, 'Maximum consumers execution per queue.', 1)
            ->addOption('max', 'm', InputOption::VALUE_OPTIONAL, 'Maximum consumers execution total.', 1)
            ->setDescription('Checks the load balance from current consumers.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        $queues = $this->getContainer()->get('task_rabbit_mq.management')->getQueues();

        if (empty($queues)) {
            // no queues, do nothing
            return $this->setOutput(0);
        }

        // all consumers listen from all queues
        // so one of them contains the consumers count.
        $consumersRunning = $queues[0]->consumers;

        // check limit
        $consumersLimit = $input->getOption('max');
        if ($consumersRunning >= $consumersLimit) {
            return $this->setOutput(0);
        }

        $nonEmptyQueues = 0;
        foreach ($queues as $queue) {
            if ($queue->messages > 0) {
                ++$nonEmptyQueues;
            }
        }

        // total consumers needed
        $consumersNeeded = $nonEmptyQueues * $input->getOption('per');

        // avoid to load too much consumers
        if ($consumersNeeded > $consumersLimit) {
            $consumersNeeded = $consumersLimit;
        }

        // negative balance means we need more consumers
        return $this->setOutput($consumersRunning - $consumersNeeded);
    }

    private function setOutput($balance)
    {
        $this->output->writeln($balance);

        // success exit code.
        return 0;
    }
}
