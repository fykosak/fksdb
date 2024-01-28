<?php

declare(strict_types=1);

namespace FKSDB\Components\Schedule\Rests;

use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Nette\DI\Container;

class TeamRestsComponent extends BaseComponent
{
    private TeamModel2 $team;

    public function __construct(Container $container, TeamModel2 $team)
    {
        parent::__construct($container);
        $this->team = $team;
    }

    final public function render(): void
    {
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'team.latte', ['team' => $this->team]);
    }
}
