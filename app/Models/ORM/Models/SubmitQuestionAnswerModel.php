<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\Model\Model;
use Nette\Utils\DateTime;

/**
 * @property-read int $answer_id
 * @property-read int $contestant_id
 * @property-read ContestantModel $contestant
 * @property-read int $submit_question_id
 * @property-read SubmitQuestionModel $submit_question
 * @property-read DateTime $submitted_on
 * @property-read string $answer
 */
final class SubmitQuestionAnswerModel extends Model
{
}
