<?php
/**
 * Async sockets
 *
 * @copyright Copyright (c) 2015-2017, Efimov Evgenij <edefimov.it@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Tests\AsyncSockets\RequestExecutor;

use AsyncSockets\RequestExecutor\EventHandlerFromSymfonyEventDispatcher;
use AsyncSockets\RequestExecutor\ExecutionContext;

/**
 * Class EventHandlerFromSymfonyEventDispatcherTest
 */
class EventHandlerFromSymfonyEventDispatcherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * testInvokeEvent
     *
     * @return void
     */
    public function testInvokeEvent()
    {
        if (!class_exists('Symfony\Component\EventDispatcher\EventDispatcher')) {
            self::markTestSkipped('To complete this test you should have symfony/event-dispatcher installed');
        }

        $type = md5(microtime());

        $event = $this->getMockBuilder('AsyncSockets\Event\Event')->setMethods(['getType'])
                            ->disableOriginalConstructor()
                            ->getMock();
        $event->expects(self::once())->method('getType')->willReturn($type);

        $dispatcher = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcher')
                        ->setMethods(['dispatch'])
                        ->getMockForAbstractClass();

        $dispatcher->expects(self::once())
            ->method('dispatch')
            ->with($type, $event);

        /** @var \Symfony\Component\EventDispatcher\EventDispatcher $dispatcher */
        /** @var \AsyncSockets\Event\Event $event */
        $object = new EventHandlerFromSymfonyEventDispatcher($dispatcher);
        $object->invokeEvent(
            $event,
            $this->getMockBuilder('AsyncSockets\RequestExecutor\RequestExecutorInterface')->getMockForAbstractClass(),
            $this->getMockBuilder('AsyncSockets\Socket\SocketInterface')->getMockForAbstractClass(),
            new ExecutionContext()
        );
    }
}
