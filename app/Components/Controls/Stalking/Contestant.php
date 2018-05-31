<?php

namespace FKSDB\Components\Controls\Stalking;

class Contestant extends StalkingComponent {

    public function render() {
        $template = $this->template;
        $this->template->contestants = $this->modelPerson->getContestants();
        $template->setFile(__DIR__ . '/Contestant.latte');
        $template->render();
    }
}
