<?php

namespace FKSDB\ORM\Models;

use FKSDB\ORM\AbstractModelSingle;
use Nette\Database\Table\ActiveRow;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property-read integer contribution_id
 * @property-read int task_id
 * @property-read int person_id
 * @property-read ActiveRow person
 * @property-read ActiveRow task
 */
class ModelTaskContribution extends AbstractModelSingle implements IPersonReferencedModel, ITaskReferencedModel {

    const TYPE_AUTHOR = 'author';
    const TYPE_SOLUTION = 'solution';
    const TYPE_GRADE = 'grade';

    /**
     * @inheritDoc
     */
    public function getPerson(): ModelPerson {
        return ModelPerson::createFromActiveRow($this->person);
    }

    /**
     * @inheritDoc
     */
    public function getTask(): ModelTask {
        return ModelTask::createFromActiveRow($this->task);
    }
}
