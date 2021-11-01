<?php

namespace FKSDB\Models\Pipeline;

use Fykosak\Utils\Logging\Logger;
use Fykosak\Utils\Logging\MemoryLogger;
use Fykosak\Utils\Logging\Message;
use Nette\InvalidStateException;

/**
 * Represents a simple pipeline where each stage has its input and output and they
 * comprise a linear chain.
 *
 * @todo Implement generic ILogger.
 */
class Pipeline {

    /** @var Stage[] */
    private array $stages = [];

    /** @var mixed */
    private $input;

    private bool $fixedStages = false;

    private ?Logger $logger = null;

    public function setLogger(Logger $logger): void {
        $this->logger = $logger;
    }

    /**
     * @return MemoryLogger
     */
    public function getLogger(): Logger {
        return $this->logger;
    }

    /**
     * Stages can be added only in the build phase (not after setting the data).
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
