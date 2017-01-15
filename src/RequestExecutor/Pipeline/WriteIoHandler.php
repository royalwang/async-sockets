<?php
/**
 * Async sockets
 *
 * @copyright Copyright (c) 2015-2017, Efimov Evgenij <edefimov.it@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace AsyncSockets\RequestExecutor\Pipeline;

use AsyncSockets\Event\WriteEvent;
use AsyncSockets\Operation\InProgressWriteOperation;
use AsyncSockets\Operation\OperationInterface;
use AsyncSockets\Operation\WriteOperation;
use AsyncSockets\RequestExecutor\EventHandlerInterface;
use AsyncSockets\RequestExecutor\Metadata\RequestDescriptor;
use AsyncSockets\RequestExecutor\RequestExecutorInterface;
use AsyncSockets\Socket\SocketInterface;

/**
 * Class WriteIoHandler
 */
class WriteIoHandler extends AbstractOobHandler
{
    /** {@inheritdoc} */
    public function supports(OperationInterface $operation)
    {
        return ($operation instanceof WriteOperation) || ($operation instanceof InProgressWriteOperation);
    }

    /** {@inheritdoc} */
    protected function handleOperation(
        RequestDescriptor $descriptor,
        RequestExecutorInterface $executor,
        EventHandlerInterface $eventHandler
    ) {
        $operation = $descriptor->getOperation();
        $socket    = $descriptor->getSocket();

        /** @var WriteOperation $operation */
        $fireEvent = !($operation instanceof InProgressWriteOperation);

        if ($fireEvent) {
            $meta  = $executor->socketBag()->getSocketMetaData($socket);
            $event = new WriteEvent(
                $operation,
                $executor,
                $socket,
                $meta[ RequestExecutorInterface::META_USER_CONTEXT ]
            );
            $eventHandler->invokeEvent($event);
            $nextOperation = $event->getNextOperation();
        } else {
            $nextOperation = $operation;
        }

        $result = $this->writeDataToSocket($operation, $socket, $nextOperation, $bytesWritten);
        $this->handleTransferCounter(RequestDescriptor::COUNTER_SEND_MIN_RATE, $descriptor, $bytesWritten);

        return $result;
    }

    /**
     * Perform data writing to socket and return suitable next socket operation
     *
     * @param WriteOperation     $operation Current write operation instance
     * @param SocketInterface    $socket Socket object
     * @param OperationInterface $nextOperation Desirable next operation
     * @param int                $bytesWritten Amount of written bytes
     *
     * @return OperationInterface Actual next operation
     */
    private function writeDataToSocket(
        WriteOperation $operation,
        SocketInterface $socket,
        OperationInterface $nextOperation = null,
        &$bytesWritten = null
    ) {
        $result               = $nextOperation;
        $extractNextOperation = true;
        $bytesWritten         = 0;

        if ($operation->hasData()) {
            $data    = $operation->getData();
            $length  = strlen($data);
            $written = $socket->write($data, $operation->isOutOfBand());
            if ($length !== $written) {
                $extractNextOperation = false;

                $operation = $this->makeInProgressWriteOperation($operation, $nextOperation);
                $operation->setData(
                    substr($operation->getData(), $written)
                );

                $result = $operation;
            }

            $bytesWritten = $written;
        }

        if ($extractNextOperation && ($operation instanceof InProgressWriteOperation)) {
            /** @var InProgressWriteOperation $operation */
            $result = $operation->getNextOperation();
        }

        return $result;
    }

    /**
     * Marks write operation as in progress
     *
     * @param WriteOperation     $operation Current operation object
     * @param OperationInterface $nextOperation Next planned operation
     *
     * @return InProgressWriteOperation Next operation object
     */
    private function makeInProgressWriteOperation(WriteOperation $operation, OperationInterface $nextOperation = null)
    {
        $result = $operation;
        if (!($result instanceof InProgressWriteOperation)) {
            $result = new InProgressWriteOperation($nextOperation, $operation->getData());
        }

        return $result;
    }
}
