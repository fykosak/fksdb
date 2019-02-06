<?php

namespace FKSDB\Components\Controls\Stalking;

/**
 * Class Flag
 * @package FKSDB\Components\Controls\Stalking
 */
class Flag extends StalkingComponent {

    public function render() {
        $this->beforeRender();
        $this->template->flags = $this->modelPerson->getMPersonHasFlags();
        $this->template->setFile(__DIR__ . '/Flag.latte');
        $this->template->render();
    }
}
