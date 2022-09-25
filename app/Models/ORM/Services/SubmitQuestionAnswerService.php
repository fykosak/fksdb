<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\SubmitQuestionModel;
use FKSDB\Models\ORM\Models\SubmitQuestionAnswerModel;
use Nette\Utils\DateTime;
use Fykosak\NetteORM\Service;

class SubmitQuestionAnswerService extends Service
{

    public function findByContestant(
        SubmitQuestionModel $question,
        ContestantModel $contestant
    ): ?SubmitQuestionAnswerModel {
        return $contestant->related(DbNames::TAB_SUBMIT_QUESTION_ANSWER)
            ->where('question_id', $question->submit_question_id)
            ->fetch();
    }

    public function saveSubmittedQuestion(
        SubmitQuestionModel $question,
        ContestantModel $contestant,
        ?string $answer
    ): void {
        $submit = $this->findByContestant($question, $contestant);
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
