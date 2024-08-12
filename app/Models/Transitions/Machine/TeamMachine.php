<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Machine;

use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Services\Fyziklani\TeamService2;
use FKSDB\Models\Transitions\Holder\TeamHolder;
use Fykosak\NetteORM\Model\Model;

/**
 * @phpstan-extends Machine<TeamHolder>
 */
final class TeamMachine extends Machine
{
    private TeamService2 $teamService;

    public function __construct(TeamService2 $teamService)
    {
        $this->teamService = $teamService;
    }

    /**
     * @param TeamModel2 $model
     */
    public function createHolder(Model $model): TeamHolder
    {
        return new TeamHolder($model, $this->teamService);
    }
}
