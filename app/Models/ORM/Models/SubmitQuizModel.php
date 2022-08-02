<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\Model;

/**
 * @property-read int submit_question_id
 * @property-read int ct_id
 * @property-read int task_id
 * @property-read TaskModel task
 * @property-read ContestantModel contestant_base
 * @property-read int question_id
 * @property-read \DateTimeInterface submitted_on
 * @property-read string answer
 */
class SubmitQuizModel extends Model
{
}
