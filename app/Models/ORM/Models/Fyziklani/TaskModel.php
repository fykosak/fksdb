<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Fyziklani;

use FKSDB\Models\ORM\Models\ModelEvent;
use Fykosak\NetteORM\Model;
use Nette\Database\Table\ActiveRow;

/**
 * @property-read string name
 * @property-read string label
 * @property-read int fyziklani_task_id
 * @property-read int event_id
 * @property-read ActiveRow event
 */
class TaskModel extends Model
{

    public function getEvent(): ModelEvent
    {
        return ModelEvent::createFromActiveRow($this->event);
    }

    public function __toArray(bool $hideName = false): array
    {
        return [
            'label' => $this->label,
            'points' => 5,
            'taskId' => $this->fyziklani_task_id,
            'name' => $hideName ? null : $this->name,
        ];
    }
}
