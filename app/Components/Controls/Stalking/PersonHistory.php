<?php

namespace FKSDB\Components\Controls\Stalking;

/**
 * Class PersonHistory
 * @package FKSDB\Components\Controls\Stalking
 */
class PersonHistory extends StalkingComponent {

    public function render() {
        $this->beforeRender();
        $this->template->data = [];
        $this->template->data['history'] = $this->modelPerson->related(\DbNames::TAB_PERSON_HISTORY, 'person_id');
        $this->template->setFile(__DIR__ . '/PersonHistory.latte');
        $this->template->render();
    }
}
