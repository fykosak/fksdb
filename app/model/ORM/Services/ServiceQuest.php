<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelQuest;

/**
 *
 * @author Miroslav Jarý <mira.jary@gmail.com>
 *
 */
class ServiceQuest extends AbstractServiceSingle {
    /**
     *
     * {@inheritDoc}
     * @see \FKSDB\ORM\AbstractServiceSingle::getModelClassName()
     */
    public function getModelClassName(): string {
        return ModelQuest::class;
    }
    /**
     *
     * {@inheritDoc}
     * @see \FKSDB\ORM\AbstractServiceSingle::getTableName()
     */
    protected function getTableName(): string {
        return DbNames::TAB_QUEST;
    }
    
    public function findByTask($task, $questnr) {
        $result = $this->getTable()->where([
            'task_id' => $task->task_id,
            'questnr' => $questnr,
        ])->fetch();
        
        if ($result !== false) {
            return ModelQuest::createFromActiveRow($result);
        } else {
            return null;
        }
    }
}
