<?php

namespace FKSDB\Models\ORM\Services\Fyziklani;

use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTask;
use FKSDB\Models\ORM\Models\ModelEvent;
use Fykosak\NetteORM\AbstractService;
use Fykosak\NetteORM\TypedTableSelection;

/**
 * @author Lukáš Timko <lukast@fykos.cz>
 */
class ServiceFyziklaniTask extends AbstractService {

    public function findByLabel(string $label, ModelEvent $event): ?ModelFyziklaniTask {
        /** @var ModelFyziklaniTask $result */
        $result = $this->getTable()->where([
            'label' => $label,
            'event_id' => $event->event_id,
        ])->fetch();
        return $result;
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
