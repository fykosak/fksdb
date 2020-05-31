<?php

namespace Pipeline;

use FKSDB\Messages\Message;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class Stage {

    private Pipeline $pipeline;

    /**
     * @param mixed $data data to process
     */
    abstract public function setInput($data): void;

    abstract public function process(): void;

    /**
     * @return mixed output of the stage
     */
    abstract public function getOutput();

    final protected function getPipeline(): Pipeline {
        return $this->pipeline;
    }

    final public function setPipeline(Pipeline $pipeline): void {
        $this->pipeline = $pipeline;
    }

    final protected function log(Message $message): void {
        $this->getPipeline()->log($message);
    }
}
