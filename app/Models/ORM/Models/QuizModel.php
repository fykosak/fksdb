<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\Model;

/**
 * @property-read TaskModel task
 * @property-read int task_id
 * @property-read int question_id
 * @property-read int question_nr
 */
class QuizModel extends Model
{

    public function getFQName(): string
    {
        return sprintf(_('%s. question'), $this->question_nr);
    }

    public function getContest(): ContestModel
    {
        return $this->task->contest;
    }
}
