<?php

declare(strict_types=1);

namespace FKSDB\Components\PDFGenerators\TeamSeating\AllTeams;

use FKSDB\Components\PDFGenerators\TeamSeating\SeatingProviderComponent;
use FKSDB\Models\ORM\Models\ModelEvent;
use Nette\DI\Container;

class AllTeamsProviderComponent extends SeatingProviderComponent
{
    private string $mode;

    public function __construct(ModelEvent $event, string $mode, Container $container)
    {
        parent::__construct($event, $container);
        $this->mode = $mode;
    }

    public function createComponentPage(): AllTeamsPageComponent
    {
        return new AllTeamsPageComponent($this->mode, $this->getContext());
    }

    public function getItems(): iterable
    {
        return [null];
    }

    public function getFormat(): string
    {
        return AllTeamsProviderComponent::FORMAT_A5;
    }
}
