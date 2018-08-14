<?php

namespace FKSDB\Components\Controls\Stalking;

class EventParticipant extends StalkingComponent {

    public function render() {
        $template = $this->template;
        $this->template->participants = $this->modelPerson->getEventParticipant();
        $template->setFile(__DIR__ . '/EventParticipant.latte');
        $template->render();
    }
}
