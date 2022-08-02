<?php

declare(strict_types=1);

namespace FKSDB\Components\Charts\Event\ParticipantAcquaintance;

use FKSDB\Components\Charts\Core\Chart;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use Fykosak\NetteFrontendComponent\Components\FrontEndComponent;
use Nette\DI\Container;

class ParticipantAcquaintanceChart extends FrontEndComponent implements Chart
{

    private EventModel $event;

    public function __construct(Container $context, EventModel $event)
    {
        parent::__construct($context, 'chart.events.participants.acquaintance');
        $this->event = $event;
    }

    public function getData(): array
    {
        $data = [];
        foreach ($this->event->getParticipants()->where('status', ['participated', 'applied']) as $row) {
            $participant = EventParticipantModel::createFromActiveRow($row);

            $participants = [];
            foreach ($participant->person->getEventParticipants()->where('status', ['participated']) as $item) {
                $personParticipation = EventParticipantModel::createFromActiveRow($item);
                $participants[] = $personParticipation->event->event_id;
            }
            $datum = [
                'person' => [
                    'name' => $participant->person->getFullName(),
                    'gender' => $participant->person->gender->value,
                ],
                'participation' => $participants,
            ];
            $data[] = $datum;
        }
        return $data;
    }

    public function getTitle(): string
    {
        return _('Participant acquaintance');
    }

    public function getDescription(): ?string
    {
        return null;
    }
}
