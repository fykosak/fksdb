<?php

namespace FKSDB\Payment\PriceCalculator\PreProcess;

use Exception;
use FKSDB\ORM\Models\ModelPayment;
use FKSDB\Payment\Price;
use FKSDB\Payment\PriceCalculator\UnsupportedCurrencyException;

/**
 * Class SchedulePrice
 * @package FKSDB\Payment\PriceCalculator\PreProcess
 */
class SchedulePrice implements IPreprocess {
    /**
     * @param ModelPayment $modelPayment
     * @return Price
     * @throws UnsupportedCurrencyException
     */
    public static function calculate(ModelPayment $modelPayment): Price {
        $price = new Price(0, $modelPayment->currency);
        foreach ($modelPayment->getRelatedPersonSchedule() as $model) {
            $modelPrice = $model->getScheduleItem()->getPrice($modelPayment->currency);
            $price->add($modelPrice);
        }
        return $price;
    }

    /**
     * @param ModelPayment $modelPayment
     * @return array
     * @throws UnsupportedCurrencyException
     * @throws Exception
     */
    public static function getGridItems(ModelPayment $modelPayment): array {
        $items = [];

        foreach ($modelPayment->getRelatedPersonSchedule() as $model) {
            $scheduleItem = $model->getScheduleItem();
            $items[] = [
                'label' => $model->getLabel(),
                'price' => $scheduleItem->getPrice($modelPayment->currency),
            ];
        }
        return $items;
    }
}
