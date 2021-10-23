<?php

namespace FKSDB\Models\ORM\Services\Fyziklani;

use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Services\OldAbstractServiceSingle;

/**
 * @method ModelFyziklaniTeam|null findByPrimary($key)
 */
class ServiceFyziklaniTeam extends OldAbstractServiceSingle {

    /**
     * @return ModelFyziklaniTeam[]
     */
    public static function getTeamsAsArray(ModelEvent $event): array {
        $teams = [];
        foreach ($event->getPossiblyAttendingTeams() as $row) {
            $team = ModelFyziklaniTeam::createFromActiveRow($row);
            $teams[] = $team->__toArray(true);
        }
        return $teams;
    }

    public function isCategoryReadyForClosing(ModelEvent $event, string $category = null): bool {
        $query = $event->getParticipatingTeams();
        if ($category) {
            $query->where('category', $category);
        }
        $query->where('points', null);
        return $query->count() == 0;
    }
}
