<?php

declare(strict_types=1);

namespace FKSDB\Models\Payment\PriceCalculator\PreProcess;

use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\Payment\PriceCalculator\UnsupportedCurrencyException;
use Fykosak\Utils\Price\MultiCurrencyPrice;

class SchedulePrice implements Preprocess
{
    /**
     * @throws UnsupportedCurrencyException|\Exception
     */
    public static function calculate(PaymentModel $modelPayment): MultiCurrencyPrice
    {
        $price = MultiCurrencyPrice::createFromCurrencies([$modelPayment->getCurrency()]);
        foreach ($modelPayment->getRelatedPersonSchedule() as $model) {
            $modelPrice = $model->schedule_item->getPrice();
            $price->add($modelPrice);
        }
        return $price;
    }

    /**
     * @throws UnsupportedCurrencyException
     * @throws NotImplementedException
     * @throws \Exception
     */
    public static function getGridItems(PaymentModel $modelPayment): array
    {
        $items = [];

        foreach ($modelPayment->getRelatedPersonSchedule() as $model) {
            $scheduleItem = $model->schedule_item;
            $items[] = [
                'label' => $model->getLabel(),
                'price' => $scheduleItem->getPrice(),
            ];
        }
        return $items;
    }
}
