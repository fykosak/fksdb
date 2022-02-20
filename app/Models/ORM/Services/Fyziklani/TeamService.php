<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services\Fyziklani;

use FKSDB\Models\ORM\Models\Fyziklani\TeamCategory;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Services\OldAbstractServiceSingle;

/**
 * @method TeamModel|null findByPrimary($key)
 */
class TeamService extends OldAbstractServiceSingle
{

    /**
     * @return TeamModel[]
     */
    public static function serialiseTeams(ModelEvent $event): array
    {
        $teams = [];
        foreach ($event->getPossiblyAttendingTeams() as $row) {
            $team = TeamModel::createFromActiveRow($row);
            $teams[] = $team->__toArray();
        }
        return $teams;
    }

    public function isCategoryReadyForClosing(ModelEvent $event, ?TeamCategory $category = null): bool
    {
        $query = $event->getParticipatingTeams();
        if ($category) {
            $query->where('category', $category->value);
        }
        $query->where('points', null);
        return $query->count() == 0;
    }
}
