<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services\Fyziklani;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TaskModel;
use Fykosak\NetteORM\Service;

/**
 * @method TaskModel storeModel(array $data, ?TaskModel $model = null)
 */
final class TaskService extends Service
{

    public function findByLabel(string $label, EventModel $event): ?TaskModel
    {
        return $event->getTasks()->where([
            'label' => $label,
        ])->fetch();
    }

    /**
     * @return TaskModel[]
     */
    public static function serialiseTasks(EventModel $event, bool $hideName = false): array
    {
        $tasks = [];
        /** @var TaskModel $model */
        foreach ($event->getTasks()->order('label') as $model) {
            $tasks[] = $model->__toArray($hideName);
        }
        return $tasks;
    }
}
