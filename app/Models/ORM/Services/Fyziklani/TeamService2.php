<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services\Fyziklani;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamCategory;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use Fykosak\NetteORM\Service;

/**
 * @phpstan-extends Service<TeamModel2>
 * @phpstan-import-type SerializedTeamModel from TeamModel2
 */
final class TeamService2 extends Service
{

    public function isReadyForClosing(EventModel $event, ?TeamCategory $category = null): bool
    {
        $query = $event->getParticipatingTeams();
        if ($category) {
            $query->where('category', $category->value);
        }
        $query->where('points', null);
        return $query->count() == 0;
    }

    /**
     * @phpstan-return SerializedTeamModel[]
     */
    public static function serialiseTeams(EventModel $event): array
    {
        $teams = [];
        /** @var TeamModel2 $team */
        foreach ($event->getPossiblyAttendingTeams() as $team) {
            $teams[] = $team->__toArray();
        }
        return $teams;
    }
}
