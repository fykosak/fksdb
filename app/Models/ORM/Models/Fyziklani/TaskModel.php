<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Fyziklani;

use FKSDB\Models\ORM\Models\EventModel;
use Fykosak\NetteORM\Model;

/**
 * @property-read string name
 * @property-read string label
 * @property-read int fyziklani_task_id
 * @property-read int points
 * @property-read int event_id
 * @property-read EventModel event
 */
class TaskModel extends Model
{
    public function __toArray(bool $hideName = false): array
    {
        return [
            'label' => $this->label,
            'points' => $this->points ?? 5, // FOF defaults
            'taskId' => $this->fyziklani_task_id,
            'name' => $hideName ? null : $this->name,
        ];
    }
}
