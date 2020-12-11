<?php

namespace FKSDB\Components\Controls\Schedule\Rests;

use Fykosak\Utils\BaseComponent\BaseComponent;
use FKSDB\Model\ORM\Models\ModelEvent;
use FKSDB\Model\ORM\Models\ModelPerson;

/**
 * Class SingleRestComponent
 * @author Michal Červeňák <miso@fykos.cz>
 */
class SingleRestComponent extends BaseComponent {

    public function render(ModelPerson $person, ModelEvent $event): void {
        $this->template->rests = $person->getScheduleRests($event);
        $this->template->person = $person;
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'layout.person.latte');
        $this->template->render();
    }
}
