<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\QuizModel;
use FKSDB\Models\ORM\Models\SubmitQuizModel;
use Nette\Utils\DateTime;
use Fykosak\NetteORM\Service;

class SubmitQuizService extends Service
{

    public function findByContestant(QuizModel $question, ContestantModel $contestant): ?SubmitQuizModel
    {
        return $contestant->related(DbNames::TAB_SUBMIT_QUIZ)
            ->where('question_id', $question->question_id)
            ->fetch();
    }

    public function saveSubmittedQuestion(QuizModel $question, ContestantModel $contestant, ?string $answer): void
    {
        $submit = $this->findByContestant($question, $contestant);
        if ($submit) {
            $this->storeModel([
                'submitted_on' => new DateTime(),
                'answer' => $answer,
            ], $submit);
        } else {
            $this->storeModel([
                'question_id' => $question->question_id,
                'contestant_id' => $contestant->contestant_id,
                'submitted_on' => new DateTime(),
                'answer' => $answer,
            ]);
        }
    }
}
