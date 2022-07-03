<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services\Fyziklani;

use FKSDB\Models\ORM\Models\Fyziklani\TeamModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Services\OldServiceSingle;

/**
 * @method TeamModel|null findByPrimary($key)
 * @deprecated
 */
class TeamService extends OldServiceSingle
{

    /**
     * @return TeamModel[]
     */
    public static function serialiseTeams(ModelEvent $event): array
    {
        $teams = [];
        foreach ($event->getPossiblyAttendingFyziklaniTeams() as $row) {
            $team = TeamModel2::createFromActiveRow($row);
            $teams[] = $team->__toArray();
        }
        return $teams;
    }
}
