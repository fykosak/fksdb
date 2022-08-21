<?php

declare(strict_types=1);

namespace FKSDB\Components\Charts\Event\Applications;

use FKSDB\Components\Charts\Core\Chart;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use Fykosak\NetteFrontendComponent\Components\FrontEndComponent;
use Nette\DI\Container;

class ParticipantsTimeGeoChart extends FrontEndComponent implements Chart
{

    protected EventModel $event;

    public function __construct(Container $context, EventModel $event)
    {
        parent::__construct($context, 'chart.events.participants.time-geo');
        $this->event = $event;
    }

    public function getTitle(): string
    {
        return _('Participants per country');
    }

    protected function getData(): array
    {
        $rawData = [];
        /** @var EventParticipantModel $participant */
        foreach ($this->event->getParticipants() as $participant) {
            $iso = $participant->getPersonHistory()->school->address->region->country_iso3;
            $rawData[] = [
                'country' => $iso,
                'created' => $participant->created->format('c'),
            ];
        }
        return $rawData;
    }

    public function getDescription(): ?string
    {
        return null;
    }
}
