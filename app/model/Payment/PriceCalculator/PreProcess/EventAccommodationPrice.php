<?php

namespace FKSDB\Payment\PriceCalculator\PreProcess;

use FKSDB\ORM\Models\ModelEventAccommodation;
use FKSDB\ORM\Models\ModelEventPersonAccommodation;
use FKSDB\ORM\Models\ModelPayment;
use FKSDB\Payment\Price;
use FKSDB\Payment\PriceCalculator\UnsupportedCurrencyException;

/**
 * Class EventAccommodationPrice
 * @package FKSDB\Payment\PriceCalculator\PreProcess
 */
class EventAccommodationPrice extends AbstractPreProcess {
    /**
     * @param ModelPayment $modelPayment
     * @return \FKSDB\Payment\Price
     * @throws UnsupportedCurrencyException
     */
    public static function calculate(ModelPayment $modelPayment): Price {
        $price = new Price(0, $modelPayment->currency);
        foreach ($modelPayment->getRelatedPersonAccommodation() as $row) {
            $eventAcc = ModelEventPersonAccommodation::createFromActiveRow($row)->getEventAccommodation();
            $modelPrice = self::getPriceFromModel($eventAcc, $price);
            $price->add($modelPrice);
        }
        return $price;
    }

    /**
     * @param ModelPayment $modelPayment
     * @return array
     * @throws UnsupportedCurrencyException
     * @throws \Exception
     */
    public static function getGridItems(ModelPayment $modelPayment): array {
        $price = new Price(0, $modelPayment->currency);
        $items = [];

        foreach ($modelPayment->getRelatedPersonAccommodation() as $row) {
            $model = ModelEventPersonAccommodation::createFromActiveRow($row);
            $eventAcc = $model->getEventAccommodation();
            $items[] = [
                'label' => $model->getLabel(),
                'price' => self::getPriceFromModel($eventAcc, $price),
            ];
        }
        return $items;
    }

    /**
     * @param \FKSDB\ORM\Models\ModelEventAccommodation $modelEventAccommodation
     * @param \FKSDB\Payment\Price $price
     * @return \FKSDB\Payment\Price
     * @throws UnsupportedCurrencyException
     */
    private static function getPriceFromModel(ModelEventAccommodation $modelEventAccommodation, Price &$price): Price {
        switch ($price->getCurrency()) {
            case Price::CURRENCY_CZK:
                $amount = $modelEventAccommodation->price_kc;
                break;
            case Price::CURRENCY_EUR:
                $amount = $modelEventAccommodation->price_eur;
                break;
            default:
                throw new UnsupportedCurrencyException($price->getCurrency(), 501);
        }
        return new Price($amount, $price->getCurrency());
    }
}
