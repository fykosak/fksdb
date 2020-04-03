<?php

namespace Pipeline;

use FKSDB\Logging\ILogger;
use FKSDB\Logging\MemoryLogger;
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

    /**
     * @var array of IStage
     */
    private $stages = [];

    /**
     * @var mixed
     */
    private $input;

    /**
     * @var bool
     */
    private $fixedStages = false;

    /**
     * @var ILogger
     */
    private $logger = null;

    /**
     * @param ILogger $logger
     */
    public function setLogger(ILogger $logger) {
        $this->logger = $logger;
    }

    /**
     * @return MemoryLogger|MemoryLogger
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
    public function addStage(Stage $stage) {
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
    public function setInput($input) {
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

    /**
     * @param $message
     * @param string $level
     */
    public function log($message, $level = ILogger::INFO) {
        if ($this->logger) {
            $this->logger->log($message, $level);
        }
    }

}
