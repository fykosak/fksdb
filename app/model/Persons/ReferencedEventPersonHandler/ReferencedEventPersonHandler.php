<?php

namespace FKSDB\Person\Handler;

use FKSDB\Components\Forms\Controls\PersonAccommodation\Handler;
use ModelPerson;
use ServiceEventPersonAccommodation;
use ServiceMPersonHasFlag;
use ServiceMPostContact;
use ServicePerson;
use ServicePersonHistory;
use ServicePersonInfo;

class ReferencedEventPersonHandler extends ReferencedPersonHandler {
    /**
     * @var Handler
     */
    private $eventAccommodationHandler;

    /**
     * @var integer
     */
    private $eventId;

    function __construct(
        Handler $eventAccommodation,
        ServiceEventPersonAccommodation $serviceEventPersonAccommodation,
        ServicePerson $servicePerson,
        ServicePersonInfo $servicePersonInfo,
        ServicePersonHistory $servicePersonHistory,
        ServiceMPostContact $serviceMPostContact,
        ServiceMPersonHasFlag $serviceMPersonHasFlag,
        $acYear,
        $resolution,
        $eventId
    ) {
        parent::__construct(
            $eventAccommodation,
            $serviceEventPersonAccommodation,
            $servicePerson,
            $servicePersonInfo,
            $servicePersonHistory,
            $serviceMPostContact,
            $serviceMPersonHasFlag,
            $acYear,
            $resolution
        );
        $this->eventId = $eventId;
        $this->eventAccommodationHandler = $eventAccommodation;
    }


    protected function getIterators() {
        $models = parent::getIterators();
        $models[] = 'person_accommodation';
        return $models;
    }

    protected function updateItem($iterator, ModelPerson $model, array $data) {
        switch ($iterator) {
            case 'person_accommodation':
                $this->updatePersonAccommodation($model, $data);
                break;
            default:
                parent::updateItem($iterator, $model, $data);
        }
    }

    private function updatePersonAccommodation(ModelPerson $person, array $data) {
        if (isset($data)) {
            $this->eventAccommodationHandler->prepareAndUpdate($data, $person, $this->eventId);
        }
        return;
    }
}
