<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Fyziklani\Seating;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\ModelEvent;
use Fykosak\NetteORM\Model;
use Nette\Database\Table\ActiveRow;

/**
 * @property-read int fyziklani_seat_id
 * @property-read ActiveRow fyziklani_room
 * @property-read string sector
 * @property-read double layout_x
 * @property-read double layout_y
 * @property-read int fyziklani_room_id
 */
class SeatModel extends Model
{
    public function getRoom(): RoomModel
    {
        return RoomModel::createFromActiveRow($this->fyziklani_room);
    }

    public function getTeamSeat(ModelEvent $event): ?TeamSeatModel
    {
        $row = $this->related(DbNames::TAB_FYZIKLANI_TEAM_SEAT)->where(
            'fyziklani_team.event_id',
            $event->event_id
        )->fetch();
        return $row ? TeamSeatModel::createFromActiveRow($row) : null;
    }
}
