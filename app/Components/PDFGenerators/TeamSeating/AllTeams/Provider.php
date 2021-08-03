<?php

declare(strict_types=1);

namespace FKSDB\Components\PDFGenerators\TeamSeating\AllTeams;

use FKSDB\Components\PDFGenerators\Provider\ProviderComponent;
use FKSDB\Components\PDFGenerators\TeamSeating\AbstractProvider;
use FKSDB\Models\ORM\Models\ModelEvent;
use Nette\DI\Container;

class Provider extends AbstractProvider
{

    private string $mode;

    public function __construct(ModelEvent $event, string $mode, Container $container)
    {
        parent::__construct($event, $container);
        $this->mode = $mode;
    }

    public function createComponentPage(): PageComponent
    {
        return new PageComponent($this->mode, $this->container);
    }

    public function getItems(): iterable
    {
        return [null];
    }

    public function getFormat(): string
    {
        return ProviderComponent::FORMAT_A5;
    }
}
