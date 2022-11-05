<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\Model;

/**
 * @property-read int submit_question_id
 * @property-read int task_id
 * @property-read TaskModel task
 * @property-read string label
 * @property-read int points
 * @property-read string answer
 */
class SubmitQuestionModel extends Model
{
    public function getFQName(): string
    {
        return sprintf(_('%s. question'), $this->label);
    }
}
