<?php

namespace FKSDB\Components\Controls\Fyziklani\ResultsAndStatistics\ResultsTable;

use FKSDB\Components\Controls\Fyziklani\ResultsAndStatistics\ResultsAndStatisticsComponent;
use FKSDB\Models\ORM\Models\ModelEvent;
use Nette\DI\Container;

class ResultsTableComponent extends ResultsAndStatisticsComponent {

    public function __construct(Container $container, ModelEvent $event) {
        parent::__construct($container, $event, 'fyziklani.results.table');
    }
}
