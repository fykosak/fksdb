<?php

declare(strict_types=1);

namespace FKSDB\Models\Persons;

use FKSDB\Components\Forms\Referenced\ReferencedHandler;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\EventModel;
use Nette\DI\Container;
use Nette\SmartObject;

class ReferencedPersonHandlerFactory
{
    use SmartObject;

    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function create(
        ContestYearModel $contestYear,
        ?string $resolution,
        ?EventModel $event = null
    ): ReferencedPersonHandler {
        $handler = new ReferencedPersonHandler(
            $contestYear,
            $resolution ?? ReferencedHandler::RESOLUTION_EXCEPTION
        );
        if ($event) {
            $handler->setEvent($event);
        }
        $this->container->callInjects($handler);
        return $handler;
    }
}
