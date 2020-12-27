<?php

namespace FKSDB\Components\Controls\Schedule\Rests;


use Fykosak\Utils\BaseComponent\BaseComponent;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTeam;


/**
 * Class TeamRestsComponent
 * @author Michal Červeňák <miso@fykos.cz>
 */
class TeamRestsComponent extends BaseComponent {

    public function render(ModelFyziklaniTeam $team): void {
        $this->template->event = $team->getEvent();
        $this->template->persons = $team->getPersons();
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'layout.team.latte');
        $this->template->render();
    }

    protected function createComponentSingleRestControl(): SingleRestComponent {
        return new SingleRestComponent($this->getContext());
    }
}
