<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Fyziklani\Seating;

use FKSDB\Models\ORM\DbNames;
use Fykosak\NetteORM\Model;
use Fykosak\NetteORM\TypedGroupedSelection;

/**
 * @property-read int fyziklani_room_id
 * @property-read string name
 * @property-read string layout
 */
class RoomModel extends Model
{
    public function getSeats(): TypedGroupedSelection
    {
        return $this->related(DbNames::TAB_FYZIKLANI_SEAT);
    }

    public function __toArray(): array
    {
        return [
            'roomId' => $this->fyziklani_room_id,
            'name' => $this->name,
        ];
    }
}
