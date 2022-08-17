<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Holder;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\Fyziklani\TeamState;
use FKSDB\Models\ORM\Services\Fyziklani\TeamService2;
use Fykosak\NetteORM\Model;

class FyziklaniTeamHolder implements ModelHolder
{
    private ?TeamModel2 $team;
    private TeamService2 $service;

    public function __construct(?TeamModel2 $team, TeamService2 $service)
    {
        $this->team = $team;
        $this->service = $service;
    }

    public function updateState(EnumColumn $newState): void
    {
        $this->service->storeModel(['state' => $newState->value], $this->team);
    }

    public function getState(): ?TeamState
    {
        return isset($this->team) ? $this->team->state : null;
    }

    public function getModel(): ?Model
    {
        return $this->team;
    }

    public function updateData(array $data): void
    {
        $this->service->storeModel($data, $this->team);
    }
}
