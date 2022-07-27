<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services\Fyziklani;

use FKSDB\Models\ORM\Models\Fyziklani\TaskModel;
use FKSDB\Models\ORM\Models\ModelEvent;
use Fykosak\NetteORM\Service;

class TaskService extends Service
{

    public function findByLabel(string $label, ModelEvent $event): ?TaskModel
    {
        $result = $event->getFyziklaniTasks()->where([
            'label' => $label,
        ])->fetch();
        return $result ? TaskModel::createFromActiveRow($result) : null;
    }

    /**
     * @return TaskModel[]
     */
    public static function serialiseTasks(ModelEvent $event, bool $hideName = false): array
    {
        $tasks = [];

        foreach ($event->getFyziklaniTasks()->order('label') as $row) {
            $model = TaskModel::createFromActiveRow($row);
            $tasks[] = $model->__toArray($hideName);
        }
        return $tasks;
    }
}
