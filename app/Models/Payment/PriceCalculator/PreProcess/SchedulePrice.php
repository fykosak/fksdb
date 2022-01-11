<?php

declare(strict_types=1);

namespace FKSDB\Models\Payment\PriceCalculator\PreProcess;

use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\ModelPayment;
use FKSDB\Models\Payment\PriceCalculator\UnsupportedCurrencyException;
use Fykosak\Utils\Price\Price;

class SchedulePrice implements Preprocess
{
    /**
     * @throws UnsupportedCurrencyException|\Exception
     */
    public static function calculate(ModelPayment $modelPayment): Price
    {
        $price = new Price($modelPayment->getCurrency(), 0);
        foreach ($modelPayment->getRelatedPersonSchedule() as $model) {
            $modelPrice = $model->getScheduleItem()->getPrice($modelPayment->getCurrency());
            $price->add($modelPrice);
        }
        return $price;
    }

    /**
     * @throws UnsupportedCurrencyException
     * @throws NotImplementedException
     * @throws \Exception
     */
    public static function getGridItems(ModelPayment $modelPayment): array
    {
        $items = [];

        foreach ($modelPayment->getRelatedPersonSchedule() as $model) {
            $scheduleItem = $model->getScheduleItem();
            $items[] = [
                'label' => $model->getLabel(),
                'price' => $scheduleItem->getPrice($modelPayment->getCurrency()),
            ];
        }
        return $items;
    }
}
