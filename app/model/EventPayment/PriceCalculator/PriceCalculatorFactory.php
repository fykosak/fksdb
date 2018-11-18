<?php

namespace FKSDB\EventPayment\PriceCalculator;

use FKSDB\EventPayment\PriceCalculator\PreProcess\EventAccommodationPrice;
use FKSDB\EventPayment\PriceCalculator\PreProcess\EventPrice;
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

    public function createCalculator(ModelEvent $event, $currency) {
        $calculator = new PriceCalculator($event, $currency);
        $calculator->addPreProcess(new EventPrice($this->serviceEventParticipant, $currency));
        // $calculator->addPreProcess(new EventSchedulePrice($this->serviceEventParticipant));// TODO mergnuť s programom pre FOF
        $calculator->addPreProcess(new EventAccommodationPrice($this->serviceEventPersonAccommodation, $currency));
        return $calculator;
    }
}
