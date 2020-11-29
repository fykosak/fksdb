<?php

namespace FKSDB\Components\Controls\Chart\GeoCharts;

use FKSDB\ORM\Models\ModelEvent;
use Nette\DI\Container;

class TeamsGeoChartControl extends ApplicationsPerCountryChartControl {
    public function __construct(Container $context, ModelEvent $event) {
        parent::__construct($context, $event, self::SCALE_LOG);
    }

    public function getTitle(): string {
        return _('Teams per country');
    }

    protected function getData(): array {
        $data = [];
        foreach ($this->getTeams() as $row) {
            $data[$row->country] = [
                self::KEY_COUNT => $row->t,
            ];
        }
        return $data;
    }
}
