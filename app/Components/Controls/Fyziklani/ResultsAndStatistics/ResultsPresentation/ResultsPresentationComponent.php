<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Fyziklani\ResultsAndStatistics\ResultsPresentation;

use FKSDB\Components\Controls\Fyziklani\ResultsAndStatistics\ResultsAndStatisticsComponent;
use FKSDB\Models\ORM\Models\ModelEvent;
use Nette\DI\Container;

class ResultsPresentationComponent extends ResultsAndStatisticsComponent
{

    public function __construct(Container $container, ModelEvent $event)
    {
        parent::__construct($container, $event, 'fyziklani.results.presentation');
    }
}
