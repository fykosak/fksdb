<?php

namespace FKSDB\Components\Controls\Chart\Event\Applications;

use FKSDB\Model\ORM\Models\ModelEvent;
use Nette\DI\Container;

class ApplicationRationGeoChart extends ApplicationsPerCountryChartComponent {

    public function __construct(Container $context, ModelEvent $event) {
        parent::__construct($context, $event, 'chart.events.application-ratio.geo');
    }

    public function getTitle(): string {
        return _('Participants per team');
    }

    protected function getData(): array {
        $data = [];
        foreach ($this->getTeams() as $row) {
            $data[$row->country] = [
                self::KEY_COUNT => ($row->p / $row->t),
            ];
        }
        return $data;
    }
}
