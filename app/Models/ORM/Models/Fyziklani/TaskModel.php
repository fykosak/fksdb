<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Fyziklani;

use FKSDB\Models\Authorization\Resource\EventResource;
use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\EventModel;
use Fykosak\NetteORM\Model\Model;

/**
 * @property-read string $name
 * @property-read string $label
 * @property-read int $fyziklani_task_id
 * @property-read int $points
 * @property-read int $event_id
 * @property-read EventModel $event
 * @phpstan-type SerializedTaskModel array{
 *     label:string,
 *     points:int,
 *     taskId:int,
 *     name:string|null,
 * }
 */
final class TaskModel extends Model implements EventResource
{
    public const RESOURCE_ID = 'game.task';
    /**
     * @phpstan-return SerializedTaskModel
     */
    public function __toArray(bool $hideName = false): array
    {
        return [
            'label' => $this->label,
            'points' => $this->points ?? 5, // FOF defaults
            'taskId' => $this->fyziklani_task_id,
            'name' => $hideName ? null : $this->name,
        ];
    }

    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }

    public function getEvent(): EventModel
    {
        return $this->event;
    }
}
