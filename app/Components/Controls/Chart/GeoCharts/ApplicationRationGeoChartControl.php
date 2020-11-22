<?php

namespace FKSDB\Components\Controls\Chart;

use FKSDB\ORM\Models\ModelEvent;
use Nette\DI\Container;

class ApplicationRationGeoChartControl extends ApplicationsPerCountryChartControl {
    public function __construct(Container $context, ModelEvent $event) {
        parent::__construct($context, $event, 'ratio', self::SCALE_LINEAR);
    }

    public function getTitle(): string {
        return _('Ratio per country');
    }
}