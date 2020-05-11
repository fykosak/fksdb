<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelContestant;
use FKSDB\ORM\Models\ModelQuizQuestion;
use FKSDB\ORM\Models\ModelSubmitQuizQuestion;
use Nette\Utils\DateTime;

class ServiceSubmitQuizQuestion extends AbstractServiceSingle {
    
    /** @var array */
    private $submitCache = [];
    
    /** @return string */
    public function getModelClassName(): string {
        return ModelSubmitQuizQuestion::class;
    }
    
    /** @return string */
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
        $key = $ctId . ':' . $questionId;
        
        if (!array_key_exists($key, $this->submitCache)) {
            $result = $this->getTable()->where([
                'ct_id' => $ctId,
                'question_id' => $questionId,
            ])->fetch();
            if ($result !== false) {
                $this->submitCache[$key] = $result;
            } else {
                $this->submitCache[$key] = null;
            }
        }
        return $this->submitCache[$key];
    }
    
    public function saveSubmitedQuestion(ModelQuizQuestion $question, ModelContestant $contestant, string $answer) {
        $submit = $this->findByContestant($contestant->ct_id, $question->question_id);
        if ($submit) {
            $submit->update([
                'submitted_on' => new DateTime(),
                'answer' => $answer,
            ]);
        } else {
            $submit = $this->createNewModel([
                'question_id' => $question->question_id,
                'ct_id' => $contestant->ct_id,
                'submitted_on' => new DateTime(),
                'answer' => $answer,
            ]);
        }
    }
}