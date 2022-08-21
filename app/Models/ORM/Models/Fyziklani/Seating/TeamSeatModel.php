<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Fyziklani\Seating;

use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use Fykosak\NetteORM\Model;

/**
 * @property-read int fyziklani_seat_id
 * @property-read SeatModel fyziklani_seat
 * @property-read int fyziklani_team_id
 * @property-read TeamModel2|null fyziklani_team
 */
class TeamSeatModel extends Model
{
}
