<?php

namespace FKSDB\Pipeline;

use FKSDB\Logging\ILogger;
use FKSDB\Logging\MemoryLogger;
use FKSDB\Messages\Message;
use Nette\InvalidStateException;

/**
 * Represents a simple pipeline where each stage has its input and output and they
 * comprise a linear chain.
 *
 * @todo Implement generic ILogger.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class Pipeline {

    /** @var Stage[] */
    private array $stages = [];

    /** @var mixed */
    private $input;

    private bool $fixedStages = false;

    private ?ILogger $logger = null;

    public function setLogger(ILogger $logger): void {
        $this->logger = $logger;
    }

    /**
     * @return MemoryLogger
     */
    public function getLogger(): ILogger {
        return $this->logger;
    }

    /**
     * Stages can be added only in the build phase (not after setting the data).
     *
     * @param Stage $stage
     * @throws InvalidStateException
     */
    public function addStage(Stage $stage): void {
        if ($this->fixedStages) {
            throw new InvalidStateException('Cannot modify pipeline after loading data.');
        }
        $this->stages[] = $stage;
        $stage->setPipeline($this);
    }

    /**
     * Input to the pipeline.
     *
     * @param mixed $input
     */
    public function setInput($input): void {
        $this->fixedStages = true;
        $this->input = $input;
    }

    /**
     * Starts the pipeline.
     *
     * @return mixed    output of the last stage
     */
    public function run() {
        $data = $this->input;
        foreach ($this->stages as $stage) {
            $stage->setInput($data);
            $stage->process();
            $data = $stage->getOutput();
        }

        return $data;
    }

    public function log(Message $message): void {
        if ($this->logger) {
            $this->logger->log($message);
        }
    }
}
