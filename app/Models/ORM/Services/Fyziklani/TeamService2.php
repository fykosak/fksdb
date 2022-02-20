<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services\Fyziklani;

use FKSDB\Models\ORM\Models\Fyziklani\TeamCategory;
use FKSDB\Models\ORM\Models\ModelEvent;
use Fykosak\NetteORM\AbstractService;

class TeamService2 extends AbstractService
{

    public function isReadyForClosing(ModelEvent $event, ?TeamCategory $category = null): bool
    {
        $query = $event->getParticipatingTeams();
        if ($category) {
            $query->where('category', $category->value);
        }
        $query->where('points', null);
        return $query->count() == 0;
    }
}
