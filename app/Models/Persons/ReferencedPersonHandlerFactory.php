<?php

namespace FKSDB\Models\Persons;

use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Services\ServiceAddress;
use FKSDB\Models\ORM\Services\ServiceFlag;
use FKSDB\Models\ORM\Services\ServicePerson;
use FKSDB\Models\ORM\Services\ServicePersonHasFlag;
use FKSDB\Models\ORM\Services\ServicePersonHistory;
use FKSDB\Models\ORM\Services\ServicePersonInfo;
use FKSDB\Models\ORM\Services\ServicePostContact;
use Nette\DI\Container;
use Nette\SmartObject;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class ReferencedPersonHandlerFactory {

    use SmartObject;

    private Container $container;

    public function __construct(Container $container) {
        $this->container = $container;
    }

    public function create(int $acYear, ?string $resolution, ?ModelEvent $event = null): ReferencedPersonHandler {
        $handler = new ReferencedPersonHandler(
            $acYear,
            $resolution??ReferencedPersonHandler::RESOLUTION_EXCEPTION
        );
        if ($event) {
            $handler->setEvent($event);
        }
        $this->container->callInjects($handler);
        return $handler;
    }

}
