<?php

namespace FKSDB\ORM\Models;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\DbNames;

/**
 * 
 * @author Miroslav Jarý <mira.jary@gmail.com>
 * @property-read int submit_question_id
 * @property-read int ct_id
 * @property-read int question_id
 * @property-read DateTime submitted_on
 * @property-read string answer
 */
class ModelSubmitQuizQuestion extends AbstractModelSingle implements ITaskReferencedModel {

    /**
     * @return ModelTask
     */
    public function getTask(): ModelTask {
        return ModelTask::createFromActiveRow($this->ref(DbNames::TAB_TASK, 'task_id'));
    }

    /**
     * @return ModelContestant
     */
    public function getContestant(): ModelContestant {
        return ModelContestant::createFromActiveRow($this->ref(DbNames::TAB_CONTESTANT_BASE, 'ct_id'));
    }
}
