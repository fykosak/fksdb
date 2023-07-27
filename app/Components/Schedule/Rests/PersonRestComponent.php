<?php

declare(strict_types=1);

namespace FKSDB\Components\Schedule\Rests;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\PersonModel;
use Fykosak\Utils\BaseComponent\BaseComponent;

class PersonRestComponent extends BaseComponent
{

    final public function render(PersonModel $person, EventModel $event): void
    {
        $this->template->render(
            __DIR__ . DIRECTORY_SEPARATOR . 'person.latte',
            ['rests' => $person->getScheduleRests($event), 'person' => $person]
        );
    }
}
