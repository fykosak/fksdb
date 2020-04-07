<?php

namespace Pipeline;

use FKSDB\Logging\ILogger;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class Stage {

    /**
     * @var Pipeline
     */
    private $pipeline;

    /**
     * @param mixed $data data to process
     */
    abstract public function setInput($data);

    abstract public function process();

    /**
     * @return mixed output of the stage
     */
    abstract public function getOutput();

    /**
     * @return Pipeline
     */
    public function getPipeline() {
        return $this->pipeline;
    }

    /**
     * @param Pipeline $pipeline
     */
    public function setPipeline(Pipeline $pipeline) {
        $this->pipeline = $pipeline;
    }

    /**
     * @param $message
     * @param string $level
     */
    protected function log(string $message, string $level = ILogger::INFO) {
        $this->getPipeline()->log($message, $level);
    }
}
