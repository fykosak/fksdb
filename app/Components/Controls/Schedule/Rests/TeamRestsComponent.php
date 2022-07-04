<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Schedule\Rests;

use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use Fykosak\Utils\BaseComponent\BaseComponent;

class TeamRestsComponent extends BaseComponent
{
    final public function render(TeamModel2 $team): void
    {
        $this->template->event = $team->getEvent();
        $this->template->persons = $team->getPersons();
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.team.latte');
    }

    protected function createComponentSingleRestControl(): SingleRestComponent
    {
        return new SingleRestComponent($this->getContext());
    }
}
