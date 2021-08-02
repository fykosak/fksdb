<?php

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\AbstractModel;
use Nette\Database\Table\ActiveRow;

/**
 * @property-read ActiveRow task
 * @property-read int task_id
 * @property-read int question_id
 * @property-read int question_nr
 */
class ModelQuiz extends AbstractModel
{

    public function getFQName(): string
    {
        return sprintf(_('%s. question'), $this->question_nr);
    }

    public function getContest(): ModelContest
    {
        return $this->getTask()->getContest();
    }

    public function getTask(): ModelTask
    {
        return ModelTask::createFromActiveRow($this->task);
    }
}
