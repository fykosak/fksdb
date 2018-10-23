<?php

namespace FKSDB\Components\Controls\Stalking;

class Contestant extends StalkingComponent {

    public function render() {
        $this->beforeRender();
        $this->template->contestants = $this->modelPerson->getContestants();
        $this->template->setFile(__DIR__ . '/Contestant.latte');
        $this->template->render();
    }
}
