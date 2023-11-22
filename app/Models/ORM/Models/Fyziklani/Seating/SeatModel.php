<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Fyziklani\Seating;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\EventModel;
use Fykosak\NetteORM\Model\Model;

/**
 * @property-read int $fyziklani_seat_id
 * @property-read RoomModel $fyziklani_room
 * @property-read string $sector
 * @property-read float $layout_x
 * @property-read float $layout_y
 * @property-read int $fyziklani_room_id
 */
final class SeatModel extends Model
{

    public function getTeamSeat(EventModel $event): ?TeamSeatModel
    {
        /** @var TeamSeatModel|null $teamSeat */
        $teamSeat = $this->related(DbNames::TAB_FYZIKLANI_TEAM_SEAT)
            ->where('fyziklani_team.event_id', $event->event_id)
            ->fetch();
        return $teamSeat;
    }
}
