<?php

namespace FKSDB\Components\Controls\Chart\GeoCharts;

use FKSDB\Components\Controls\Chart\IChart;
use FKSDB\Components\React\ReactComponent;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Models\ModelEventParticipant;
use FKSDB\Models\ORM\Services\ServiceEventParticipant;
use Nette\DI\Container;

class ParticipantsInTimeGeoChartControl extends ReactComponent implements IChart {

    protected ModelEvent $event;
    protected ServiceEventParticipant $serviceEventParticipant;

    public function __construct(Container $context, ModelEvent $event) {
        parent::__construct($context, 'chart.participants-in-time-geo');
        $this->event = $event;
    }

    public function injectSecondary(ServiceEventParticipant $serviceEventParticipant): void {
        $this->serviceEventParticipant = $serviceEventParticipant;
    }

    public function getTitle(): string {
        return _('Participants per country');
    }

    protected function getData(): array {
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

    public function getDescription(): ?string {
        return null;
    }

    public function getControl(): self {
        return $this;
    }
}
