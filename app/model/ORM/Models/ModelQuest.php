<?php

namespace FKSDB\ORM\Models;

use FKSDB\ORM\AbstractModelSingle;

/**
 *
 * @author Miroslav Jarý <mira.jary@gmail.com>
 * @property-read ActiveRow task
 * @property-read int task_id
 */
class ModelQuest extends AbstractModelSingle implements IContestReferencedModel {
    
    /**
     * (Fully qualified) task name for use in GUI.
     *
     * @return string
     */
    public function getFQName(): string {
        return sprintf('%s. otázka', $this->questnr);
    }
    
    /**
     * @return ModelTask
     */
    public function getTask(): ModelTask {
        return ModelTask::createFromActiveRow($this->task);
    }

    /**
     * @return ModelContest
     */
    public function getContest(): ModelContest {
        return $this->getTask()->getContest();
    }
}
