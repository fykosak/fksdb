<?php

namespace FKSDB\Components\Controls\Stalking;

class BaseInfo extends StalkingComponent {

    public function render() {
        $this->beforeRender();
        $this->template->info = $this->modelPerson->getInfo();
        $this->template->setFile(__DIR__ . '/BaseInfo.latte');
        $this->template->render();
    }
}
