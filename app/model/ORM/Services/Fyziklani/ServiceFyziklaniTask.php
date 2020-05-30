<?php

namespace FKSDB\ORM\Services\Fyziklani;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTask;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Tables\TypedTableSelection;

/**
 * @author Lukáš Timko <lukast@fykos.cz>
 */
class ServiceFyziklaniTask extends AbstractServiceSingle {

    public function getModelClassName(): string {
        return ModelFyziklaniTask::class;
    }

    protected function getTableName(): string {
        return DbNames::TAB_FYZIKLANI_TASK;
    }

    /**
     * Syntactic sugar.
     * @param string $label
     * @param ModelEvent $event
     * @return ModelFyziklaniTask|null
     */
    public function findByLabel(string $label, ModelEvent $event) {
        /** @var ModelFyziklaniTask $result */
        $result = $this->getTable()->where([
            'label' => $label,
            'event_id' => $event->event_id,
        ])->fetch();
        return $result ?: null;
    }

    public function findAll(ModelEvent $event): TypedTableSelection {
        return $this->getTable()->where('event_id', $event->event_id);
    }

    /**
     * @param ModelEvent $event
     * @param bool $hideName
     * @return ModelFyziklaniTask[]
     */
    public function getTasksAsArray(ModelEvent $event, bool $hideName = false): array {
        $tasks = [];

        foreach ($this->findAll($event)->order('label') as $row) {
            $model = ModelFyziklaniTask::createFromActiveRow($row);
            $tasks[] = $model->__toArray($hideName);
        }
        return $tasks;
    }
}
