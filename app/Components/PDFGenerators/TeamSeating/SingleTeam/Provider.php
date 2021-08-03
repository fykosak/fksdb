<?php

declare(strict_types=1);

namespace FKSDB\Components\PDFGenerators\TeamSeating\SingleTeam;

use FKSDB\Components\PDFGenerators\Provider\ProviderComponent;
use FKSDB\Components\PDFGenerators\TeamSeating\AbstractProvider;

class Provider extends AbstractProvider
{
    public function createComponentPage(): PageComponent
    {
        return new PageComponent($this->container);
    }

    public function getItems(): iterable
    {
        return $this->event->getTeams()->limit(5);
    }

    public function getFormat(): string
    {
        return ProviderComponent::FORMAT_A5;
    }
}
