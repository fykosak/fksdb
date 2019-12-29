<?php

namespace FKSDB\Payment\PriceCalculator;

use FKSDB\ORM\Models\ModelEvent;
use FKSDB\Payment\PriceCalculator\PreProcess\SchedulePrice;
use Nette\DI\Container;
use Nette\NotImplementedException;
use Tracy\Debugger;

/**
 * Class PriceCalculatorFactory
 * @package FKSDB\Payment\PriceCalculator
 */
class PriceCalculatorFactory {
    /**
     *
     */
    private $context;

    /**
     * PriceCalculatorFactory constructor.
     * @param Container $context
     */
    public function __construct(Container $context) {
        $this->context = $context;
    }

    /**
     * @param ModelEvent $event
     * @return PriceCalculator
     * @throws \Exception
     */
    public function createCalculator(ModelEvent $event): PriceCalculator {
        $calculator = $this->context->getService('payment.priceCalculator.' . $event->event_id);
        if ($calculator instanceof PriceCalculator) {
            return $calculator;
        }
        throw new NotImplementedException();
    }
}
