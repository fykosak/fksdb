<?php

namespace FKSDB\Payment\PriceCalculator;

use FKSDB\ORM\Models\ModelEvent;
use FKSDB\Payment\PriceCalculator\PreProcess\SchedulePrice;

/**
 * Class PriceCalculatorFactory
 * @package FKSDB\Payment\PriceCalculator
 */
class PriceCalculatorFactory {
    /**
     * @param ModelEvent $event
     * @return PriceCalculator
     */
    public function createCalculator(ModelEvent $event): PriceCalculator {
        $calculator = new PriceCalculator($event);
        // $calculator->addPreProcess(new EventPrice($this->serviceEventParticipant));
        $calculator->addPreProcess(new SchedulePrice());
        return $calculator;
    }
}
