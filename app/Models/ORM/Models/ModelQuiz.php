<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Nette\Database\Table\ActiveRow;
use Fykosak\NetteORM\Model;

/**
 * @property-read ActiveRow task
 * @property-read int task_id
 * @property-read int question_id
 * @property-read int question_nr
 */
class ModelQuiz extends Model
{

    public function getFQName(): string
    {
        return sprintf(_('%s. question'), $this->question_nr);
    }

    public function getTask(): ModelTask
    {
        return ModelTask::createFromActiveRow($this->task, $this->mapper);
    }

    public function getContest(): ModelContest
    {
        return $this->getTask()->contest;
    }
}
