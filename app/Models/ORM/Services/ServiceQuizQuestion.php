<?php

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\Models\ModelQuizQuestion;
use FKSDB\Models\ORM\Models\ModelTask;

/**
 * @author Miroslav Jarý <mira.jary@gmail.com>
 */
class ServiceQuizQuestion extends AbstractServiceSingle {

    /**
     * Find question from quiz by task
     * @param ModelTask $task
     * @param int $questionNr
     * @return ModelQuizQuestion|NULL
     */
    public function findByTask(ModelTask $task, int $questionNr): ?ModelQuizQuestion {
        $result = $this->getTable()->where([
            'task_id' => $task->task_id,
            'question_nr' => $questionNr,
        ])->fetch();

        if ($result !== false) {
            return ModelQuizQuestion::createFromActiveRow($result);
        } else {
            return null;
        }
    }
}
