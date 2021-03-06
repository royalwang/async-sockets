<?php
/**
 * Async sockets
 *
 * @copyright Copyright (c) 2015-2017, Efimov Evgenij <edefimov.it@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace AsyncSockets\RequestExecutor;

use AsyncSockets\Socket\SocketInterface;

/**
 * Interface LimitationSolverInterface. Allows to limit amount of requests processing at once
 */
interface LimitationSolverInterface
{
    /**
     * Schedule given socket request
     */
    const DECISION_OK = 0;

    /**
     * Process already scheduled operations
     */
    const DECISION_PROCESS_SCHEDULED = 1;

    /**
     * Skip given request now
     */
    const DECISION_SKIP_CURRENT = 2;

    /**
     * Process initialization of request
     *
     * @param RequestExecutorInterface $executor         Request executor
     * @param ExecutionContext         $executionContext Execution context
     *
     * @return void
     */
    public function initialize(RequestExecutorInterface $executor, ExecutionContext $executionContext);

    /**
     * Process finalization of request
     *
     * @param RequestExecutorInterface $executor Request executor
     * @param ExecutionContext         $executionContext Execution context
     *
     * @return void
     */
    public function finalize(RequestExecutorInterface $executor, ExecutionContext $executionContext);

    /**
     * Decide what to do with current request
     *
     * @param RequestExecutorInterface $executor Request executor
     * @param SocketInterface          $socket Socket for operation
     * @param ExecutionContext         $executionContext Execution context
     * @param int                      $totalSockets Total amount of scheduled sockets at moment of method call
     *
     * @return int One of DECISION_* consts
     */
    public function decide(
        RequestExecutorInterface $executor,
        SocketInterface $socket,
        ExecutionContext $executionContext,
        $totalSockets
    );
}
