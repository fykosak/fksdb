<?php

namespace FKSDB\Components\Controls\Schedule\Rests;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;

/**
 * Class TeamRestsComponent
 * @author Michal Červeňák <miso@fykos.cz>
 */
class TeamRestsComponent extends BaseComponent {
    /**
     * @param ModelFyziklaniTeam $team
     * @return void
     */
    public function render(ModelFyziklaniTeam $team) {
        $this->template->event = $team->getEvent();
        $this->template->persons = $team->getPersons();
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR.'team.latte');
        $this->template->render();
    }

    protected function createComponentSingleRestControl(): SingleRestComponent {
        return new SingleRestComponent($this->getContext());
    }
}
