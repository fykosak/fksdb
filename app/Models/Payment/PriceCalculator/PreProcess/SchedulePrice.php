<?php

namespace FKSDB\Models\Payment\PriceCalculator\PreProcess;

use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\ModelPayment;
use FKSDB\Models\Payment\Price;
use FKSDB\Models\Payment\PriceCalculator\UnsupportedCurrencyException;

/**
 * Class SchedulePrice
 * @author Michal Červeňák <miso@fykos.cz>
 */
class SchedulePrice implements Preprocess {
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
