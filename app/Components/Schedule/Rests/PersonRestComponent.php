<?php

declare(strict_types=1);

namespace FKSDB\Components\Schedule\Rests;

use FKSDB\Models\ORM\Models\EventParticipantModel;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Nette\DI\Container;

final class PersonRestComponent extends BaseComponent
{
    private EventParticipantModel $eventParticipant;

    public function __construct(Container $container, EventParticipantModel $eventParticipant)
    {
        parent::__construct($container);
        $this->eventParticipant = $eventParticipant;
    }

    public function render(): void
    {
        $this->template->render(
            __DIR__ . DIRECTORY_SEPARATOR . 'person.latte',
            [
                'rests' => $this->eventParticipant->person->getScheduleRests($this->eventParticipant->event),
                'person' => $this->eventParticipant->person,
            ]
        );
    }
}
