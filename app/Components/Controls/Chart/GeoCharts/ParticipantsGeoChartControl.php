<?php

namespace FKSDB\Components\Controls\Chart;

use FKSDB\ORM\Models\ModelEvent;
use Nette\DI\Container;

class ParticipantsGeoChartControl extends ApplicationsPerCountryChartControl {
    public function __construct(Container $context, ModelEvent $event) {
        parent::__construct($context, $event, 'participants', self::SCALE_LOG);
    }

    public function getTitle(): string {
        return _('Participants per country');
    }
}