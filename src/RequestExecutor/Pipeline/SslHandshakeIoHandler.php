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

use AsyncSockets\Exception\SslHandshakeException;
use AsyncSockets\Operation\OperationInterface;
use AsyncSockets\Operation\SslHandshakeOperation;
use AsyncSockets\RequestExecutor\EventHandlerInterface;
use AsyncSockets\RequestExecutor\ExecutionContext;
use AsyncSockets\RequestExecutor\Metadata\RequestDescriptor;
use AsyncSockets\RequestExecutor\RequestExecutorInterface;

/**
 * Class SslHandshakeIoHandler
 */
class SslHandshakeIoHandler extends AbstractOobHandler
{
    /** {@inheritdoc} */
    public function supports(OperationInterface $operation)
    {
        return $operation instanceof SslHandshakeOperation;
    }

    /** {@inheritdoc} */
    protected function handleOperation(
        OperationInterface $operation,
        RequestDescriptor $descriptor,
        RequestExecutorInterface $executor,
        EventHandlerInterface $eventHandler,
        ExecutionContext $executionContext
    ) {
        $socket = $descriptor->getSocket();

        /** @var SslHandshakeOperation $operation */
        $resource = $descriptor->getSocket()->getStreamResource();
        $result   = stream_socket_enable_crypto($resource, true, $operation->getCipher());
        if ($result === true) {
            return $operation->getNextOperation();
        } elseif ($result === false) {
            throw new SslHandshakeException($socket, 'SSL handshake failed.');
        }

        return $operation;
    }

    /**
     * @inheritDoc
     */
    protected function getHandlerType()
    {
        return RequestDescriptor::RDS_WRITE;
    }
}
