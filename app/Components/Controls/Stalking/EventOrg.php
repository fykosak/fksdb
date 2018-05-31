<?php

namespace FKSDB\Components\Controls\Stalking;

class EventOrg extends StalkingComponent {

    public function render() {
        $template = $this->template;
        $this->template->orgs = $this->modelPerson->getEventOrg();
        $template->setFile(__DIR__ . '/EventOrg.latte');
        $template->render();
    }
}
