<?php

namespace FKSDB\ORM\Models;

use FKSDB\ORM\AbstractModelSingle;
use Nette\Database\Table\ActiveRow;

/**
 *
 * @author Miroslav Jarý <mira.jary@gmail.com>
 * @property-read ActiveRow task
 * @property-read int task_id
 * @property-read int question_id
 */
class ModelQuizQuestion extends AbstractModelSingle implements IContestReferencedModel {

    /**
     * (Fully qualified) question name for use in GUI
     * @return string
     */
    public function getFQName(): string {
        return sprintf(_('%s. otázka'), $this->question_nr);
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
