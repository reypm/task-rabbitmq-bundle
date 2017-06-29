<?php

/*
 * This file is part of the TaskRabbitMqBundle.
 *
 * (c) Yonel Ceruto <yonelceruto@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yceruto\TaskRabbitMqBundle\EventListener\Doctrine;

use Doctrine\Common\Persistence\ObjectManager;
use OldSound\RabbitMqBundle\Event\AMQPEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ClearEntityManagerListener implements EventSubscriberInterface
{
    private $objectManager;

    public function __construct(ObjectManager $objectManager = null)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            AMQPEvent::AFTER_PROCESSING_MESSAGE => 'clearEntityManager',
        );
    }

    public function clearEntityManager()
    {
        if ($this->objectManager) {
            $this->objectManager->clear();
        }
    }
}
