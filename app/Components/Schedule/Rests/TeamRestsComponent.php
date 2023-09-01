<?php

declare(strict_types=1);

namespace FKSDB\Components\Schedule\Rests;

use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use Fykosak\Utils\BaseComponent\BaseComponent;

class TeamRestsComponent extends BaseComponent
{
    final public function render(TeamModel2 $team): void
    {
        $this->template->render(
            __DIR__ . DIRECTORY_SEPARATOR . 'team.latte',
            ['event' => $team->event, 'persons' => $team->getPersons()]
        );
    }

    protected function createComponentSingleRestControl(): PersonRestComponent
    {
        return new PersonRestComponent($this->getContext());
    }
}
