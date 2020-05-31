<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelQuizQuestion;
use FKSDB\ORM\Models\ModelTask;

/**
 * @author Miroslav Jarý <mira.jary@gmail.com>
 */
class ServiceQuizQuestion extends AbstractServiceSingle {

    public function getModelClassName(): string {
        return ModelQuizQuestion::class;
    }

    protected function getTableName(): string {
        return DbNames::TAB_QUIZ;
    }

    public function findByTask(ModelTask $task, int $questionNr): ?ModelQuizQuestion {
        /** @var ModelQuizQuestion $result */
        $result = $this->getTable()->where([
            'task_id' => $task->task_id,
            'question_nr' => $questionNr,
        ])->fetch();
        return $result ?: null;

    }
}
