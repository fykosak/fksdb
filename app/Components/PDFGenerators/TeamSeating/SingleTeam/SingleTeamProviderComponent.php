<?php

declare(strict_types=1);

namespace FKSDB\Components\PDFGenerators\TeamSeating\SingleTeam;

use FKSDB\Components\PDFGenerators\Provider\AbstractProviderComponent;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use Nette\DI\Container;

class SingleTeamProviderComponent extends AbstractProviderComponent
{
    protected ModelFyziklaniTeam $team;

    public function __construct(ModelFyziklaniTeam $team, Container $container)
    {
        parent::__construct($container);
        $this->team = $team;
    }

    public function createComponentPage(): SingleTeamPageComponent
    {
        return new SingleTeamPageComponent($this->getContext());
    }

    public function getItems(): iterable
    {
        return [$this->team];
    }

    public function getFormat(): string
    {
        return AbstractProviderComponent::FORMAT_A5;
    }
}
