<?php

namespace FKSDB\Components\Controls\Stalking;

class Address extends StalkingComponent {

    public function render() {
        $this->beforeRender();
        $this->template->MAddress = $this->modelPerson->getMPostContacts();
        $this->template->setFile(__DIR__ . '/Address.latte');
        $this->template->render();
    }
}
