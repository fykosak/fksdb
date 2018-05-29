<?php

namespace FKSDB\Components\Controls\Stalking;

class Flag extends StalkingComponent {

    public function render() {
        $template = $this->template;
        $this->template->flags = $this->modelPerson->getMPersonHasFlags();
        $template->setFile(__DIR__ . '/Flag.latte');
        $template->render();
    }
}
