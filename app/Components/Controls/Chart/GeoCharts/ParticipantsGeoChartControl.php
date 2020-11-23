<?php

namespace FKSDB\Components\Controls\Chart;

use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelEventParticipant;
use FKSDB\ORM\Services\ServiceEventParticipant;
use Nette\DI\Container;

class ParticipantsGeoChartControl extends GeoChartControl {

    protected ModelEvent $event;
    protected ServiceEventParticipant $serviceEventParticipant;

    public function __construct(Container $context, ModelEvent $event) {
        parent::__construct($context, self::SCALE_LOG);
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
            $iso = ModelEventParticipant::createFromActiveRow($row)->getPersonHistory()->getSchool()->getAddress()->getRegion()->country_iso3;
            $rawData[$iso] = $rawData[$iso] ?? 0;
            $rawData[$iso]++;
        }

        $data = [];
        foreach ($rawData as $iso => $count) {
            $data[$iso] = [
                self::KEY_COUNT => $count,
            ];
        }
        return $data;
    }

    public function getDescription(): ?string {
        return null;
    }
}
