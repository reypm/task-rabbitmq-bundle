<?php

/*
 * This file is part of the TaskRabbitMqBundle.
 *
 * (c) Yonel Ceruto <yonelceruto@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yceruto\TaskRabbitMqBundle\Tests\EventListener\Doctrine;

use Doctrine\Common\Persistence\ObjectManager;
use OldSound\RabbitMqBundle\Event\AMQPEvent;
use PHPUnit\Framework\TestCase;
use Yceruto\TaskRabbitMqBundle\EventListener\Doctrine\ClearEntityManagerListener;

class ClearEntityManagerListenerTest extends TestCase
{
    public function testClearEntityManager()
    {
        $om = $this->getMockForAbstractClass(ObjectManager::class);
        $om->expects($this->once())->method('clear');

        $listener = new ClearEntityManagerListener($om);
        $listener->clearEntityManager();

        $subscribedEvents = $listener::getSubscribedEvents();
        $this->assertArrayHasKey(AMQPEvent::AFTER_PROCESSING_MESSAGE, $subscribedEvents);
    }
}
