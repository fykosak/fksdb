<?php

namespace FKSDB\Components\Controls\Stalking;

class Address extends StalkingComponent {

    public function render() {
        $template = $this->template;
        $this->template->MAddress = $this->modelPerson->getMPostContacts();
        $template->setFile(__DIR__ . '/Address.latte');
        $template->render();
    }
}
