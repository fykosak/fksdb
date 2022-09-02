<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Schedule\Rests;

use Fykosak\Utils\BaseComponent\BaseComponent;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\PersonModel;

class SingleRestComponent extends BaseComponent
{

    final public function render(PersonModel $person, EventModel $event): void
    {
        $this->getTemplate()->rests = $person->getScheduleRests($event);
        $this->getTemplate()->person = $person;
        $this->getTemplate()->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.person.latte');
    }
}
