<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Nette\Database\Table\ActiveRow;
use Fykosak\NetteORM\Model;

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
class ModelSubmitQuiz extends Model
{

    public function getTask(): ModelTask
    {
        return ModelTask::createFromActiveRow($this->task, $this->mapper);
    }

    public function getContestant(): ModelContestant
    {
        return ModelContestant::createFromActiveRow($this->contestant_base, $this->mapper);
    }
}
