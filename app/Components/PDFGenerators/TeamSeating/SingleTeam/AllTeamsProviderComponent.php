<?php

declare(strict_types=1);

namespace FKSDB\Components\PDFGenerators\TeamSeating\SingleTeam;

use FKSDB\Components\PDFGenerators\Provider\AbstractProviderComponent;
use FKSDB\Components\PDFGenerators\TeamSeating\SeatingProviderComponent;

class AllTeamsProviderComponent extends SeatingProviderComponent
{
    public function createComponentPage(): SingleTeamPageComponent
    {
        return new SingleTeamPageComponent($this->getContext());
    }

    public function getItems(): iterable
    {
        return $this->event->getTeams()->limit(5);
    }

    public function getFormat(): string
    {
        return AbstractProviderComponent::FORMAT_A5;
    }
}
