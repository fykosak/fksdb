<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelContestant;
use FKSDB\ORM\Models\ModelQuizQuestion;
use FKSDB\ORM\Models\ModelSubmitQuizQuestion;
use Nette\Utils\DateTime;

/**
 * @author Miroslav Jarý <mira.jary@gmail.com>
 */
class ServiceSubmitQuizQuestion extends AbstractServiceSingle {

    public function getModelClassName(): string {
        return ModelSubmitQuizQuestion::class;
    }

    protected function getTableName(): string {
        return DbNames::TAB_SUBMIT_QUIZ;
    }

    /**
     *
     * @param int $ctId
     * @param int $questionId
     * @return ModelSubmitQuizQuestion|null
     */
    public function findByContestant(int $ctId, int $questionId) {
        /** @var ModelSubmitQuizQuestion $result */
        $result = $this->getTable()->where([
            'ct_id' => $ctId,
            'question_id' => $questionId,
        ])->fetch();
        return $result ?: null;
    }

    /**
     * @param ModelQuizQuestion $question
     * @param ModelContestant $contestant
     * @param string $answer
     */
    public function saveSubmitedQuestion(ModelQuizQuestion $question, ModelContestant $contestant, string $answer) {
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
