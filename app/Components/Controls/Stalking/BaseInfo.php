<?php

namespace FKSDB\Components\Controls\Stalking;

class BaseInfo extends StalkingComponent {

    public function render() {
        $template = $this->template;
        $this->template->info = $this->modelPerson->getInfo();
        $template->setFile(__DIR__ . '/BaseInfo.latte');
        $template->render();
    }
}
