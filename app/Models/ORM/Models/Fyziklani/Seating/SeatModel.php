<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Fyziklani\Seating;

use Fykosak\NetteORM\AbstractModel;
use Nette\Database\Table\ActiveRow;

/**
 * @property-read int fyziklani_seat_id
 * @property-read ActiveRow fyziklani_room
 * @property-read string sector
 * @property-read int fyziklani_room_id
 */
class SeatModel extends AbstractModel
{
    public function getRoom(): RoomModel
    {
        return RoomModel::createFromActiveRow($this->fyziklani_room);
    }
}
