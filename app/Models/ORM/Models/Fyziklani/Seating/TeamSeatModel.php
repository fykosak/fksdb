<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Fyziklani\Seating;

use FKSDB\Models\ORM\Models\Fyziklani\TeamModel;
use Fykosak\NetteORM\AbstractModel;
use Nette\Database\Table\ActiveRow;

/**
 * @property-read int fyziklani_seat_id
 * @property-read ActiveRow fyziklani_seat
 * @property-read int e_fyziklani_team_id
 * @property-read ActiveRow e_fyziklani_team
 */
class TeamSeatModel extends AbstractModel
{

    public function getSeat(): SeatModel
    {
        return SeatModel::createFromActiveRow($this->fyziklani_seat);
    }

    public function getTeam(): ?TeamModel
    {
        if ($this->e_fyziklani_team_id) {
            return TeamModel::createFromActiveRow($this->e_fyziklani_team);
        }
        return null;
    }
}
