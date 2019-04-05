<?php

namespace FKSDB\Payment\PriceCalculator;

use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\ServiceEventParticipant;
use FKSDB\ORM\Services\ServiceEventPersonAccommodation;
use FKSDB\Payment\PriceCalculator\PreProcess\EventAccommodationPrice;

/**
 * Class PriceCalculatorFactory
 * @package FKSDB\Payment\PriceCalculator
 */
class PriceCalculatorFactory {
    /**
     * @var ServiceEventParticipant
     */
    private $serviceEventParticipant;
    /**
     * @var ServiceEventPersonAccommodation
     */
    private $serviceEventPersonAccommodation;

    /**
     * PriceCalculatorFactory constructor.
     * @param ServiceEventPersonAccommodation $serviceEventPersonAccommodation
     * @param ServiceEventParticipant $serviceEventParticipant
     */
    public function __construct(ServiceEventPersonAccommodation $serviceEventPersonAccommodation, ServiceEventParticipant $serviceEventParticipant) {
        $this->serviceEventParticipant = $serviceEventParticipant;
        $this->serviceEventPersonAccommodation = $serviceEventPersonAccommodation;
    }

    /**
     * @param \FKSDB\ORM\Models\ModelEvent $event
     * @return PriceCalculator
     */
    public function createCalculator(ModelEvent $event): PriceCalculator {
        $calculator = new PriceCalculator($event);
        // $calculator->addPreProcess(new EventPrice($this->serviceEventParticipant));
        // $calculator->addPreProcess(new EventSchedulePrice($this->serviceEventParticipant));// TODO mergnuť s programom pre FOF
        $calculator->addPreProcess(new EventAccommodationPrice());
        return $calculator;
    }
}
