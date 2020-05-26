<?php

namespace Pipeline;

use FKSDB\Messages\Message;

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
    final protected function getPipeline(): Pipeline {
        return $this->pipeline;
    }

    /**
     * @param Pipeline $pipeline
     */
    final public function setPipeline(Pipeline $pipeline) {
        $this->pipeline = $pipeline;
    }

    /**
     * @param Message $message
     */
    final protected function log(Message $message) {
        $this->getPipeline()->log($message);
    }
}
