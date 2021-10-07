<?php

namespace FKSDB\Models\Payment\PriceCalculator\PreProcess;

use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\ModelPayment;
use FKSDB\Models\Payment\Price;
use FKSDB\Models\Payment\PriceCalculator\UnsupportedCurrencyException;

class SchedulePrice implements Preprocess {
    /**
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
     * @throws UnsupportedCurrencyException
     * @throws NotImplementedException
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
