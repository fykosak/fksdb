<?php

declare(strict_types=1);

namespace FKSDB\Components\Schedule\Rests;

use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use Fykosak\Utils\BaseComponent\BaseComponent;

class TeamRestsComponent extends BaseComponent
{
    final public function render(TeamModel2 $team): void
    {
        $this->template->event = $team->event;
        $this->template->persons = $team->getPersons();
        /** @phpstan-ignore-next-line */
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'team.latte');
    }

    protected function createComponentSingleRestControl(): PersonRestComponent
    {
        return new PersonRestComponent($this->getContext());
    }
}
