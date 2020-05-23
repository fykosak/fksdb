<?php

namespace FKSDB\Components\Controls\Schedule\Rests;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;

/**
 * Class TeamRestsControl
 * @package FKSDB\Components\Controls\Fyziklani
 */
class TeamRestsComponent extends BaseComponent {
    /**
     * @param ModelFyziklaniTeam $team
     */
    public function render(ModelFyziklaniTeam $team) {
        $this->template->event = $team->getEvent();
        $this->template->persons = $team->getPersons();
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR.'team.latte');
        $this->template->render();
    }

    /**
     * @return SingleRestComponent
     */
    public function createComponentSingleRestControl(): SingleRestComponent {
        return new SingleRestComponent($this->getContext());
    }
}
