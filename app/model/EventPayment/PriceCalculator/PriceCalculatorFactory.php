<?php

namespace FKSDB\EventPayment\PriceCalculator;

use FKSDB\EventPayment\PriceCalculator\PreProcess\EventAccommodationPrice;
use FKSDB\EventPayment\PriceCalculator\PreProcess\EventPrice;
use FKSDB\EventPayment\PriceCalculator\PreProcess\EventSchedulePrice;
use FKSDB\ORM\ModelEvent;

class PriceCalculatorFactory {
    /**
     * @var \ServiceEventParticipant
     */
    private $serviceEventParticipant;
    /**
     * @var \ServiceEventPersonAccommodation
     */
    private $serviceEventPersonAccommodation;

    public function __construct(\ServiceEventPersonAccommodation $serviceEventPersonAccommodation, \ServiceEventParticipant $serviceEventParticipant) {
        $this->serviceEventParticipant = $serviceEventParticipant;
        $this->serviceEventPersonAccommodation = $serviceEventPersonAccommodation;
    }

    public function createCalculator(ModelEvent $event) {
        $calculator = new PriceCalculator($event);
        $calculator->addPreProcess(new EventPrice($this->serviceEventParticipant));
        $calculator->addPreProcess(new EventSchedulePrice($this->serviceEventParticipant));
        $calculator->addPreProcess(new EventAccommodationPrice($this->serviceEventPersonAccommodation));
        return $calculator;
    }


}
