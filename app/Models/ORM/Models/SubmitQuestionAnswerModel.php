<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\Model;

/**
 * @property-read int answer_id
 * @property-read int contestant_id
 * @property-read ContestantModel contestant
 * @property-read int submit_question_id
 * @property-read SubmitQuestionModel submit_question
 * @property-read \DateTimeInterface submitted_on
 * @property-read string answer
 */
class SubmitQuestionAnswerModel extends Model
{
}
