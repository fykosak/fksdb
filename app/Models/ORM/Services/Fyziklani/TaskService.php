<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services\Fyziklani;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TaskModel;
use Fykosak\NetteORM\Service\Service;

/**
 * @phpstan-extends Service<TaskModel>
 * @phpstan-import-type SerializedTaskModel from TaskModel
 */
final class TaskService extends Service
{
    public function findByLabel(string $label, EventModel $event): ?TaskModel
    {
        /** @var TaskModel|null $task */
        $task = $event->getTasks()->where([
            'label' => $label,
        ])->fetch();
        return $task;
    }

    /**
     * @phpstan-return SerializedTaskModel[]
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
