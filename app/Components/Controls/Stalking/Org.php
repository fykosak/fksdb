<?php

namespace FKSDB\Components\Controls\Stalking;

class Org extends StalkingComponent {

    public function render() {
        $this->beforeRender();
        $this->template->orgs = $this->modelPerson->getOrgs();
        $this->template->setFile(__DIR__ . '/Org.latte');
        $this->template->render();
    }
}
