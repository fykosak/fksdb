<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelQuizQuestion;
use FKSDB\ORM\Models\ModelTask;

class ServiceQuizQuestion extends AbstractServiceSingle {
    
    /**
     * 
     * @return string
     */
    public function getModelClassName(): string {
        return ModelQuizQuestion::class;
    }
    
    /**
     * 
     * @return string
     */
    protected function getTableName(): string {
        return DbNames::TAB_QUIZ;
    }
    
    /**
     * Find question from quiz by task
     * @param ModelTask $task
     * @param int $questionNr
     * @return ModelQuizQuestion|NULL
     */
    public function findByTask(ModelTask $task, int $questionnr) {
        $result = $this->getTable()->where([
            'task_id' => $task->task_id,
            'question_nr' =>$questionnr,
        ])->fetch();
        
        if ($result !== false) {
            return ModelQuizQuestion::createFromActiveRow($result);
        } else {
            return null;
        }
    }
}