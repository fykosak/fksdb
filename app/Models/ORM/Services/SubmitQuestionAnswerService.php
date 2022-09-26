<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\SubmitQuestionModel;
use Nette\Utils\DateTime;
use Fykosak\NetteORM\Service;

class SubmitQuestionAnswerService extends Service
{
    public function saveSubmittedQuestion(
        SubmitQuestionModel $question,
        ContestantModel $contestant,
        ?string $answer
    ): void {
        $submit = $contestant->getAnswers($question);
        if ($submit) {
            $this->storeModel([
                'submitted_on' => new DateTime(),
                'answer' => $answer,
            ], $submit);
        } else {
            $this->storeModel([
                'submit_question_id' => $question->submit_question_id,
                'contestant_id' => $contestant->contestant_id,
                'submitted_on' => new DateTime(),
                'answer' => $answer,
            ]);
        }
    }
}
