<?php

declare(strict_types=1);

namespace FKSDB\Components\Charts\Event\ParticipantAcquaintance;

use FKSDB\Components\Charts\Core\Chart;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Models\ModelEventParticipant;
use Fykosak\NetteFrontendComponent\Components\FrontEndComponent;
use Nette\DI\Container;

class ParticipantAcquaintanceChart extends FrontEndComponent implements Chart
{

    private ModelEvent $event;

    public function __construct(Container $context, ModelEvent $event)
    {
        parent::__construct($context, 'chart.events.participants.acquaintance');
        $this->event = $event;
    }

    public function getData(): array
    {
        $data = [];
        foreach ($this->event->getParticipants()->where('status', ['participated', 'applied']) as $row) {
            $participant = ModelEventParticipant::createFromActiveRow($row, $this->event->mapper);

            $participants = [];
            foreach ($participant->person->getEventParticipants()->where('status', ['participated']) as $item) {
                $personParticipation = ModelEventParticipant::createFromActiveRow($item, $this->event->mapper);
                $participants[] = $personParticipation->event->event_id;
            }
            $datum = [
                'person' => [
                    'name' => $participant->person->getFullName(),
                    'gender' => $participant->person->gender,
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
