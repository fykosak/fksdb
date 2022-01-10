<?php

namespace FKSDB\Models\ORM\Services\Fyziklani;

use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTask;
use FKSDB\Models\ORM\Models\ModelEvent;
use Fykosak\NetteORM\AbstractService;

class ServiceFyziklaniTask extends AbstractService {

    public function findByLabel(string $label, ModelEvent $event): ?ModelFyziklaniTask {
        $result = $event->getFyziklaniTasks()->where([
            'label' => $label,
        ])->fetch();
        return $result ? ModelFyziklaniTask::createFromActiveRow($result) : null;
    }

    /**
     * @return ModelFyziklaniTask[]
     */
    public function serialiseTasks(ModelEvent $event, bool $hideName = false): array {
        $tasks = [];

        foreach ($event->getFyziklaniTasks()->order('label') as $row) {
            $model = ModelFyziklaniTask::createFromActiveRow($row);
            $tasks[] = $model->__toArray($hideName);
        }
        return $tasks;
    }
}
