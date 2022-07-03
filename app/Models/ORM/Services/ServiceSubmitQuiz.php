<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\ModelContestant;
use FKSDB\Models\ORM\Models\ModelQuiz;
use FKSDB\Models\ORM\Models\ModelSubmitQuiz;
use Nette\Utils\DateTime;
use Fykosak\NetteORM\Service;

class ServiceSubmitQuiz extends Service
{

    public function findByContestant(ModelQuiz $question, ModelContestant $contestant): ?ModelSubmitQuiz
    {
        /** @var ModelSubmitQuiz $result */
        $result = $contestant->related(DbNames::TAB_SUBMIT_QUIZ)
            ->where('question_id', $question->question_id)
            ->fetch();
        return $result ? ModelSubmitQuiz::createFromActiveRow($result) : null;
    }

    public function saveSubmittedQuestion(ModelQuiz $question, ModelContestant $contestant, ?string $answer): void
    {
        $submit = $this->findByContestant($question, $contestant);
        if ($submit) {
            $this->updateModel($submit, [
                'submitted_on' => new DateTime(),
                'answer' => $answer,
            ]);
        } else {
            $this->createNewModel([
                'question_id' => $question->question_id,
                'ct_id' => $contestant->ct_id,
                'submitted_on' => new DateTime(),
                'answer' => $answer,
            ]);
        }
    }
}
