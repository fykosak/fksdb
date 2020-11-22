<?php

namespace FKSDB\Components\Controls\Chart;

use FKSDB\ORM\Models\ModelEvent;
use Nette\DI\Container;

class TeamsGeoChartControl extends ApplicationsPerCountryChartControl {
    public function __construct(Container $context, ModelEvent $event) {
        parent::__construct($context, $event, 'teams', self::SCALE_LOG);
    }

    public function getTitle(): string {
        return _('Teams per country');
    }
}
