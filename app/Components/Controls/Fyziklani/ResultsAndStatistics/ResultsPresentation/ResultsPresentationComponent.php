<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Fyziklani\ResultsAndStatistics\ResultsPresentation;

use FKSDB\Components\Controls\Fyziklani\ResultsAndStatistics\ResultsAndStatisticsComponent;
use FKSDB\Models\ORM\Models\EventModel;
use Nette\DI\Container;

class ResultsPresentationComponent extends ResultsAndStatisticsComponent
{
    public function __construct(Container $container, EventModel $event)
    {
        parent::__construct($container, $event, 'fyziklani.results.presentation');
    }
}
