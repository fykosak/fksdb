<?php

namespace FKSDB\Models\ORM\Models;

use Nette\Database\Table\ActiveRow;
use Fykosak\NetteORM\AbstractModel;

/**
 *
 * @author Miroslav Jarý <mira.jary@gmail.com>
 * @property-read ActiveRow task
 * @property-read int task_id
 * @property-read int question_id
 * @property-read int question_nr
 */
class ModelQuizQuestion extends AbstractModel {

    public function getFQName(): string {
        return sprintf(_('%s. question'), $this->question_nr);
    }

    public function getTask(): ModelTask {
        return ModelTask::createFromActiveRow($this->task);
    }

    public function getContest(): ModelContest {
        return $this->getTask()->getContest();
    }
}
