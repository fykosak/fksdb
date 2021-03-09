<?php

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\Models\ModelContestant;
use FKSDB\Models\ORM\Models\ModelQuizQuestion;
use FKSDB\Models\ORM\Models\ModelSubmitQuizQuestion;
use Nette\Utils\DateTime;
use Fykosak\NetteORM\AbstractService;

/**
 * @author Miroslav Jarý <mira.jary@gmail.com>
 */
class ServiceSubmitQuizQuestion extends AbstractService {

    public function findByContestant(int $ctId, int $questionId): ?ModelSubmitQuizQuestion {
        /** @var ModelSubmitQuizQuestion $result */
        $result = $this->getTable()->where([
            'ct_id' => $ctId,
            'question_id' => $questionId,
        ])->fetch();
        return $result;
    }

    public function saveSubmittedQuestion(ModelQuizQuestion $question, ModelContestant $contestant, ?string $answer): void {
        $submit = $this->findByContestant($contestant->ct_id, $question->question_id);
        if ($submit) {
            $this->updateModel2($submit, [
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
