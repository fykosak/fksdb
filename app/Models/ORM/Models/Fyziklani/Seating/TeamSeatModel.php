<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Fyziklani\Seating;

use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use Fykosak\NetteORM\Model;
use Nette\Database\Table\ActiveRow;

/**
 * @property-read int fyziklani_seat_id
 * @property-read ActiveRow fyziklani_seat
 * @property-read int fyziklani_team_id
 * @property-read ActiveRow fyziklani_team
 */
class TeamSeatModel extends Model
{

    public function getSeat(): SeatModel
    {
        return SeatModel::createFromActiveRow($this->fyziklani_seat);
    }

    public function getTeam(): ?TeamModel2
    {
        if ($this->fyziklani_team_id) {
            return TeamModel2::createFromActiveRow($this->fyziklani_team);
        }
        return null;
    }
}
