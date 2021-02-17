<?php

namespace FKSDB\Models\ORM\Models;

use Nette\Database\Table\ActiveRow;

/**
 *
 * @author Miroslav Jarý <mira.jary@gmail.com>
 * @property-read int submit_question_id
 * @property-read int ct_id
 * @property-read int task_id
 * @property-read ActiveRow task
 * @property-read ActiveRow contestant_base
 * @property-read int question_id
 * @property-read \DateTimeInterface submitted_on
 * @property-read string answer
 */
class ModelSubmitQuizQuestion extends AbstractModelSingle {

    public function getTask(): ModelTask {
        return ModelTask::createFromActiveRow($this->task);
    }

    public function getContestant(): ModelContestant {
        return ModelContestant::createFromActiveRow($this->contestant_base);
    }
}
