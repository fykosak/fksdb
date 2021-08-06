<?php

namespace FKSDB\Models\ORM\Models;

use Nette\Database\Table\ActiveRow;
use Fykosak\NetteORM\AbstractModel;

/**
 * @property-read int contribution_id
 * @property-read int task_id
 * @property-read int person_id
 * @property-read ActiveRow person
 * @property-read ActiveRow task
 */
class ModelTaskContribution extends AbstractModel {

    public const TYPE_AUTHOR = 'author';
    public const TYPE_SOLUTION = 'solution';
    public const TYPE_GRADE = 'grade';

    public function getPerson(): ModelPerson {
        return ModelPerson::createFromActiveRow($this->person);
    }

    public function getTask(): ModelTask {
        return ModelTask::createFromActiveRow($this->task);
    }

    public function getContest(): ModelContest {
        return $this->getTask()->getContest();
    }
}
