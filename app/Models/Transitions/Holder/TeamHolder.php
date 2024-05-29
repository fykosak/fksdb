<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Holder;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\Fyziklani\TeamState;
use FKSDB\Models\ORM\Services\Fyziklani\TeamService2;
use FKSDB\Models\Utils\FakeStringEnum;

/**
 * @phpstan-implements ModelHolder<TeamState,TeamModel2>
 */
class TeamHolder implements ModelHolder
{
    private TeamModel2 $team;
    private TeamService2 $service;

    public function __construct(TeamModel2 $team, TeamService2 $service)
    {
        $this->team = $team;
        $this->service = $service;
    }

    /** @phpstan-param EnumColumn&FakeStringEnum $newState */
    public function setState(EnumColumn $newState): void
    {
        $this->service->storeModel(['state' => $newState->value], $this->team);
    }

    public function getState(): TeamState
    {
        return $this->team->state;
    }

    public function getModel(): TeamModel2
    {
        return $this->team;
    }
}
