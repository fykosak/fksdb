<?php

namespace FKSDB\ORM\Services;


use FKSDB\ORM\DbNames;
use FKSDB\ORM\DeprecatedLazyService;
use FKSDB\ORM\Models\ModelQuizQuestion;
use FKSDB\ORM\Models\ModelTask;
use Nette\Database\Context;
use Nette\Database\IConventions;

/**
 * @author Miroslav Jarý <mira.jary@gmail.com>
 */
class ServiceQuizQuestion extends AbstractServiceSingle {
    use DeprecatedLazyService;

    public function __construct(Context $connection, IConventions $conventions) {
        parent::__construct($connection, $conventions, DbNames::TAB_QUIZ, ModelQuizQuestion::class);
    }

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
