<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Schedule\Rests;

use Fykosak\Utils\BaseComponent\BaseComponent;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Models\ModelPerson;

class SingleRestComponent extends BaseComponent
{

    final public function render(ModelPerson $person, ModelEvent $event): void
    {
        $this->template->rests = $person->getScheduleRests($event);
        $this->template->person = $person;
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.person.latte');
    }
}
