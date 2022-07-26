<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Nette\Database\Table\ActiveRow;
use Fykosak\NetteORM\Model;

/**
 * @property-read int contribution_id
 * @property-read int task_id
 * @property-read int person_id
 * @property-read ModelPerson person
 * @property-read ModelTask task
 */
class ModelTaskContribution extends Model
{

    public const TYPE_AUTHOR = 'author';
    public const TYPE_SOLUTION = 'solution';
    public const TYPE_GRADE = 'grade';

    public function getContest(): ModelContest
    {
        return $this->task->contest;
    }
}
