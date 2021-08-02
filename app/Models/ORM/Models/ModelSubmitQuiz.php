<?php

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\AbstractModel;
use Nette\Database\Table\ActiveRow;

/**
 * @property-read int submit_question_id
 * @property-read int ct_id
 * @property-read int task_id
 * @property-read ActiveRow task
 * @property-read ActiveRow contestant_base
 * @property-read int question_id
 * @property-read \DateTimeInterface submitted_on
 * @property-read string answer
 */
class ModelSubmitQuiz extends AbstractModel
{

    public function getTask(): ModelTask
    {
        return ModelTask::createFromActiveRow($this->task);
    }

    public function getContestant(): ModelContestant
    {
        return ModelContestant::createFromActiveRow($this->contestant_base);
    }
}
