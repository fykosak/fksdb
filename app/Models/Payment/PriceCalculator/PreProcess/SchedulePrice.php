<?php

declare(strict_types=1);

namespace FKSDB\Models\Payment\PriceCalculator\PreProcess;

use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\ModelPayment;
use FKSDB\Models\Payment\PriceCalculator\UnsupportedCurrencyException;
use Fykosak\Utils\Price\MultiCurrencyPrice;

class SchedulePrice implements Preprocess
{
    /**
     * @throws UnsupportedCurrencyException|\Exception
     */
    public static function calculate(ModelPayment $modelPayment): MultiCurrencyPrice
    {
        $price = MultiCurrencyPrice::createFromCurrencies([$modelPayment->getCurrency()]);
        foreach ($modelPayment->getRelatedPersonSchedule() as $model) {
            $modelPrice = $model->getScheduleItem()->getPrice();
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
                'price' => $scheduleItem->getPrice(),
            ];
        }
        return $items;
    }
}
