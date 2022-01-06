<?php

declare(strict_types=1);

namespace FKSDB\Models\Persons;

use FKSDB\Models\ORM\Models\ModelContestYear;
use FKSDB\Models\ORM\Models\ModelEvent;
use Nette\DI\Container;
use Nette\SmartObject;

class ReferencedPersonHandlerFactory {

    use SmartObject;

    private Container $container;

    public function __construct(Container $container) {
        $this->container = $container;
    }

    public function create(ModelContestYear $contestYear, ?string $resolution, ?ModelEvent $event = null): ReferencedPersonHandler {
        $handler = new ReferencedPersonHandler(
            $contestYear,
            $resolution??ReferencedPersonHandler::RESOLUTION_EXCEPTION
        );
        if ($event) {
            $handler->setEvent($event);
        }
        $this->container->callInjects($handler);
        return $handler;
    }

}
