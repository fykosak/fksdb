<?php

declare(strict_types=1);

namespace FKSDB\Models\Payment\PriceCalculator\PreProcess;

use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\ORM\Models\Schedule\SchedulePaymentModel;
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
        /** @var SchedulePaymentModel $model */
        foreach ($modelPayment->getSchedulePayment() as $model) {
            $modelPrice = $model->person_schedule->schedule_item->getPrice();
            $price->add($modelPrice);
        }
        return $price;
    }
}
