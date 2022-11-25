<?php

declare(strict_types=1);

namespace FKSDB\Components\Game\ResultsAndStatistics\ResultsPresentation;

use FKSDB\Components\Game\ResultsAndStatistics\ResultsAndStatisticsComponent;
use FKSDB\Models\ORM\Models\EventModel;
use Nette\DI\Container;

class ResultsPresentationComponent extends ResultsAndStatisticsComponent
{
    public function __construct(Container $container, EventModel $event, string $eventString)
    {
        parent::__construct($container, $event, $eventString . '.results.presentation');
    }
}
