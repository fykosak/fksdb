<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services\Fyziklani;

use FKSDB\Models\ORM\Models\Fyziklani\TeamCategory;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\ModelEvent;
use Fykosak\NetteORM\AbstractService;

/**
 * @method TeamModel2 findByPrimary(int $key)
 */
class TeamService2 extends AbstractService
{

    public function isReadyForClosing(ModelEvent $event, ?TeamCategory $category = null): bool
    {
        $query = $event->getParticipatingFyziklaniTeams();
        if ($category) {
            $query->where('category', $category->value);
        }
        $query->where('points', null);
        return $query->count() == 0;
    }

    /**
     * @return TeamModel2[]
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
