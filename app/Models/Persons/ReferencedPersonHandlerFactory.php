<?php

namespace FKSDB\Models\Persons;

use FKSDB\Components\Forms\Controls\Schedule\Handler;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Services\ServiceFlag;
use FKSDB\Models\ORM\Services\ServicePerson;
use FKSDB\Models\ORM\Services\ServicePersonHasFlag;
use FKSDB\Models\ORM\Services\ServicePersonHistory;
use FKSDB\Models\ORM\Services\ServicePersonInfo;
use Nette\SmartObject;
use FKSDB\Models\ORM\ServicesMulti\ServiceMPostContact;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class ReferencedPersonHandlerFactory {
    use SmartObject;

    private ServicePerson $servicePerson;

    private ServicePersonInfo $servicePersonInfo;

    private ServicePersonHistory $servicePersonHistory;

    private ServiceMPostContact $serviceMPostContact;

    private ServicePersonHasFlag $servicePersonHasFlag;

    private Handler $eventScheduleHandler;

    private ServiceFlag $serviceFlag;

    public function __construct(
        ServicePerson $servicePerson,
        ServicePersonInfo $servicePersonInfo,
        ServicePersonHistory $servicePersonHistory,
        ServiceMPostContact $serviceMPostContact,
        ServicePersonHasFlag $servicePersonHasFlag,
        Handler $eventScheduleHandler,
        ServiceFlag $serviceFlag
    ) {
        $this->servicePerson = $servicePerson;
        $this->servicePersonInfo = $servicePersonInfo;
        $this->servicePersonHistory = $servicePersonHistory;
        $this->serviceMPostContact = $serviceMPostContact;
        $this->servicePersonHasFlag = $servicePersonHasFlag;
        $this->eventScheduleHandler = $eventScheduleHandler;
        $this->serviceFlag = $serviceFlag;
    }

    /**
     * @param int $acYear
     * @param string $resolution
     * @param ModelEvent|null $event
     * @return ReferencedPersonHandler
     */
    public function create(int $acYear, $resolution = ReferencedPersonHandler::RESOLUTION_EXCEPTION, ModelEvent $event = null): ReferencedPersonHandler {
        $handler = new ReferencedPersonHandler(
            $this->servicePerson,
            $this->servicePersonInfo,
            $this->servicePersonHistory,
            $this->serviceMPostContact,
            $this->servicePersonHasFlag,
            $this->serviceFlag,
            $this->eventScheduleHandler,
            $acYear,
            $resolution
        );
        if ($event) {
            $handler->setEvent($event);
        }
        return $handler;
    }

}
