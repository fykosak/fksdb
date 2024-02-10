<?php

declare(strict_types=1);

namespace FKSDB\Components\TeamSeating;

use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Nette\DI\Container;

class Single extends BaseComponent
{
    public TeamModel2 $team;

    public function __construct(Container $container, TeamModel2 $team)
    {
        parent::__construct($container);
        $this->team = $team;
    }

    final public function render(): void
    {
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . '@layout.latte', [
            'lang' => $this->team->game_lang->value,
            'places' => Place2024::getAll(),
            'teams' => $this->team->getPlace() ? [$this->team->getPlace()->label() => $this->team] : [],
        ]);
    }
}
