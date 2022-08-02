<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\Model;

/**
 * @property-read int contribution_id
 * @property-read int task_id
 * @property-read TaskModel task
 * @property-read int person_id
 * @property-read PersonModel person
 * @property-read string type TODO  ENUM ('author', 'solution', 'grade')
 */
class TaskContributionModel extends Model
{
    public const TYPE_AUTHOR = 'author';
    public const TYPE_SOLUTION = 'solution';
    public const TYPE_GRADE = 'grade';

    public function getContest(): ContestModel
    {
        return $this->task->contest;
    }
}
