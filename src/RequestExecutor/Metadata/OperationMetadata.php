<?php
/**
 * Async sockets
 *
 * @copyright Copyright (c) 2015, Efimov Evgenij <edefimov.it@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace AsyncSockets\RequestExecutor\Metadata;

use AsyncSockets\Event\Event;
use AsyncSockets\RequestExecutor\OperationInterface;
use AsyncSockets\RequestExecutor\EventHandlerInterface;
use AsyncSockets\Socket\ChunkSocketResponse;
use AsyncSockets\Socket\SocketInterface;
use AsyncSockets\Socket\SocketResponse;
use AsyncSockets\Socket\SocketResponseInterface;
use AsyncSockets\Socket\StreamResourceInterface;

/**
 * Class OperationMetadata
 */
class OperationMetadata implements StreamResourceInterface
{
    /**
     * Socket for this operation
     *
     * @var SocketInterface
     */
    private $socket;

    /**
     * Key-value pairs with meta information
     *
     * @var array
     */
    private $metadata;

    /**
     * Event handler object
     *
     * @var EventHandlerInterface
     */
    private $handlers;

    /**
     * Flag whether this socket request is running
     *
     * @var bool
     */
    private $isRunning;

    /**
     * Current response data for this socket
     *
     * @var ChunkSocketResponse
     */
    private $responseData;

    /**
     * Operation to perform on socket
     *
     * @var OperationInterface
     */
    private $operation;

    /**
     * OperationMetadata constructor.
     *
     * @param SocketInterface                 $socket Socket object
     * @param OperationInterface               $operation Operation to perform on socket
     * @param array                           $metadata Metadata
     * @param EventHandlerInterface $handlers Handlers for this socket
     */
    public function __construct(
        SocketInterface $socket,
        OperationInterface $operation,
        array $metadata,
        EventHandlerInterface $handlers = null
    ) {
        $this->socket    = $socket;
        $this->operation = $operation;
        $this->metadata  = $metadata;
        $this->handlers  = $handlers;
        $this->initialize();
    }

    /**
     * Return Operation
     *
     * @return OperationInterface
     */
    public function getOperation()
    {
        return $this->operation;
    }

    /**
     * Sets Operation
     *
     * @param OperationInterface $operation New operation
     *
     * @return void
     */
    public function setOperation(OperationInterface $operation)
    {
        $this->operation = $operation;
    }

    /**
     * Initialize data before request
     *
     * @return void
     */
    public function initialize()
    {
        $this->isRunning    = false;
        $this->responseData = '';
    }

    /**
     * Add part of response
     *
     * @param SocketResponseInterface $responseChunk Chunk to add
     *
     * @return void
     */
    public function addResponseChunk(SocketResponseInterface $responseChunk = null)
    {
        $this->responseData .= (string) $responseChunk;
    }

    /**
     * Create response object from collected chunks
     *
     * @param SocketResponseInterface|null $responseChunk Last chunk of whole response
     *
     * @return SocketResponseInterface
     */
    public function createResponseFromChunks(SocketResponseInterface $responseChunk = null)
    {
        if ($responseChunk) {
            $this->addResponseChunk($responseChunk);
        }
        return new SocketResponse($this->responseData);
    }

    /**
     * Return flag whether request is running
     *
     * @return boolean
     */
    public function isRunning()
    {
        return $this->isRunning;
    }

    /**
     * Sets running flag
     *
     * @param boolean $isRunning New value for IsRunning
     *
     * @return void
     */
    public function setRunning($isRunning)
    {
        $this->isRunning = $isRunning;
    }

    /**
     * Return Socket
     *
     * @return SocketInterface
     */
    public function getSocket()
    {
        return $this->socket;
    }

    /** {@inheritdoc} */
    public function getStreamResource()
    {
        return $this->socket->getStreamResource();
    }

    /**
     * Return key-value array with metadata
     *
     * @return array
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * Set metadata for given socket
     *
     * @param string|array    $key Either string or key-value array of metadata. If string, then value must be
     *                             passed in third argument, if array, then third argument will be ignored
     * @param mixed           $value Value for key or null, if $key is array
     *
     * @return void
     */
    public function setMetadata($key, $value = null)
    {
        if (!is_array($key)) {
            $this->metadata[$key] = $value;
        } else {
            $this->metadata = array_merge(
                $this->metadata,
                $key
            );
        }
    }

    /**
     * Invoke handlers with given event
     *
     * @param Event $event Event object
     *
     * @return void
     */
    public function invokeEvent(Event $event)
    {
        if ($this->handlers) {
            $this->handlers->invokeEvent($event);
        }
    }
}
