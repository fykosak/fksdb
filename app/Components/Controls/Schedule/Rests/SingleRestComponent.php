<?php

namespace FKSDB\Components\Controls\Schedule\Rests;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelPerson;

/**
 * Class SingleRestControl
 * @package FKSDB\Components\Controls\Fyziklani
 */
class SingleRestComponent extends BaseComponent {
    /**
     * @param ModelPerson $person
     * @param ModelEvent $event
     * @return void
     */
    public function render(ModelPerson $person, ModelEvent $event) {
        $this->template->rests = $person->getScheduleRests($event);
        $this->template->person = $person;
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'person.latte');
        $this->template->render();
    }
}
