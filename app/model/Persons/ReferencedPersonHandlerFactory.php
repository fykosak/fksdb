<?php

namespace Persons;

use FKSDB\Components\Forms\Controls\PersonAccommodation\Handler;
use Nette\Object;
use ServiceMPersonHasFlag;
use ServiceMPostContact;
use ServicePerson;
use ServicePersonHistory;
use ServicePersonInfo;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class ReferencedPersonHandlerFactory extends Object {

    /**
     * @var ServicePerson
     */
    private $servicePerson;

    /**
     * @var ServicePersonInfo
     */
    private $servicePersonInfo;

    /**
     * @var ServicePersonHistory
     */
    private $servicePersonHistory;

    /**
     * @var ServiceMPostContact
     */
    private $serviceMPostContact;

    /**
     * @var ServiceMPersonHasFlag
     */
    private $serviceMPersonHasFlag;
    /**
     * @var \ServiceEventPersonAccommodation
     */
    private $serviceEventPersonAccommodation;
    /**
     * @var Handler
     */
    private $eventAccommodationHandler;

    /**
     * ReferencedPersonHandlerFactory constructor.
     * @param Handler $eventAccommodationAdjustment
     * @param \ServiceEventPersonAccommodation $serviceEventPersonAccommodation
     * @param ServicePerson $servicePerson
     * @param ServicePersonInfo $servicePersonInfo
     * @param ServicePersonHistory $servicePersonHistory
     * @param ServiceMPostContact $serviceMPostContact
     * @param ServiceMPersonHasFlag $serviceMPersonHasFlag
     */
    function __construct(
        Handler $eventAccommodationAdjustment,
        \ServiceEventPersonAccommodation $serviceEventPersonAccommodation,
        ServicePerson $servicePerson,
        ServicePersonInfo $servicePersonInfo,
        ServicePersonHistory $servicePersonHistory,
        ServiceMPostContact $serviceMPostContact,
        ServiceMPersonHasFlag $serviceMPersonHasFlag
    ) {
        $this->servicePerson = $servicePerson;
        $this->servicePersonInfo = $servicePersonInfo;
        $this->servicePersonHistory = $servicePersonHistory;
        $this->serviceMPostContact = $serviceMPostContact;
        $this->serviceMPersonHasFlag = $serviceMPersonHasFlag;
        $this->serviceEventPersonAccommodation = $serviceEventPersonAccommodation;
        $this->eventAccommodationHandler = $eventAccommodationAdjustment;
    }

    /**
     * @param $acYear
     * @param string $resolution
     * @param $eventId
     * @return ReferencedPersonHandler
     */
    public function create($acYear, $resolution = ReferencedPersonHandler::RESOLUTION_EXCEPTION, $eventId) {
        $handler = new ReferencedPersonHandler(
            $this->eventAccommodationHandler,
            $this->serviceEventPersonAccommodation,
            $this->servicePerson,
            $this->servicePersonInfo,
            $this->servicePersonHistory,
            $this->serviceMPostContact,
            $this->serviceMPersonHasFlag,
            $acYear,
            $resolution
        );
        $handler->setEventId($eventId);
        return $handler;
    }

}

