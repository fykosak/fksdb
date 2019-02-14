<?php

namespace FKSDB\Components\Controls\Stalking;

/**
 * Class EventOrg
 * @package FKSDB\Components\Controls\Stalking
 */
class EventOrg extends StalkingComponent {

    public function render() {
        $this->beforeRender();
        $this->template->orgs = $this->modelPerson->getEventOrg();
        $this->template->setFile(__DIR__ . '/EventOrg.latte');
        $this->template->render();
    }
}
