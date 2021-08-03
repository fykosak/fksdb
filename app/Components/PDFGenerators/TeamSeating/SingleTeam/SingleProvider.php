<?php

declare(strict_types=1);

namespace FKSDB\Components\PDFGenerators\TeamSeating\SingleTeam;

use FKSDB\Components\PDFGenerators\Provider\ProviderComponent;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use Nette\DI\Container;

class SingleProvider implements \FKSDB\Components\PDFGenerators\Provider\Provider
{
    protected Container $container;
    protected ModelFyziklaniTeam $team;

    public function __construct(ModelFyziklaniTeam $team, Container $container)
    {
        $this->team = $team;
        $this->container = $container;
    }

    public function createComponentPage(): PageComponent
    {
        return new PageComponent($this->container);
    }

    public function getItems(): iterable
    {
        return [$this->team];
    }

    public function getFormat(): string
    {
        return ProviderComponent::FORMAT_A5;
    }
}
