<?php

namespace FKSDB\Components\Controls\Stalking;

class Org extends StalkingComponent {

    public function render() {
        $template = $this->template;
        $this->template->orgs = $this->modelPerson->getOrgs();
        $template->setFile(__DIR__ . '/Org.latte');
        $template->render();
    }
}
