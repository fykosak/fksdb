<?php

declare(strict_types=1);

namespace FKSDB\Components\Charts\Event\Applications;

use FKSDB\Components\Charts\Core\Chart;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Models\ModelEventParticipant;
use Fykosak\NetteFrontendComponent\Components\FrontEndComponent;
use Nette\DI\Container;

class ParticipantsTimeGeoChart extends FrontEndComponent implements Chart
{

    protected ModelEvent $event;

    public function __construct(Container $context, ModelEvent $event)
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
        foreach ($this->event->getParticipants() as $row) {
            $participant = ModelEventParticipant::createFromActiveRow($row);
            $iso = $participant->getPersonHistory()->getSchool()->getAddress()->getRegion()->country_iso3;
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
