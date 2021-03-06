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

/**
 * Class CompositeStage
 */
class CompositeStage implements PipelineStageInterface
{
    /**
     * Stages
     *
     * @var PipelineStageInterface[]
     */
    private $stages;

    /**
     * CompositeStage constructor.
     *
     * @param PipelineStageInterface[] $stages Stages to process by this composite
     */
    public function __construct(array $stages)
    {
        $this->stages = $stages;
    }

    /**
     * @inheritDoc
     */
    public function processStage(array $requestDescriptors)
    {
        $result = $requestDescriptors;
        foreach ($this->stages as $stage) {
            $result = $stage->processStage($result);
        }

        return $result;
    }
}
