<?php

namespace FKSDB\Payment\PriceCalculator\PreProcess;

use FKSDB\ORM\ModelEventAccommodation;
use FKSDB\ORM\ModelEventPersonAccommodation;
use FKSDB\ORM\ModelPayment;
use FKSDB\Payment\PriceCalculator\Price;
use FKSDB\Payment\PriceCalculator\UnsupportedCurrencyException;

class EventAccommodationPrice extends AbstractPreProcess {
    /**
     * @param ModelPayment $modelPayment
     * @return Price
     * @throws UnsupportedCurrencyException
     */
    public static function calculate(ModelPayment $modelPayment): Price {
        $price = new Price(0, $modelPayment->currency);
        foreach ($modelPayment->getRelatedPersonAccommodation() as $row) {
            $eventAcc = ModelEventPersonAccommodation::createFromTableRow($row)->getEventAccommodation();
            $modelPrice = self::getPriceFromModel($eventAcc, $price);
            $price->add($modelPrice);
        }
        return $price;
    }

    /**
     * @param ModelPayment $modelPayment
     * @return array
     * @throws UnsupportedCurrencyException
     */
    public static function getGridItems(ModelPayment $modelPayment): array {
        $price = new Price(0, $modelPayment->currency);
        $items = [];

        foreach ($modelPayment->getRelatedPersonAccommodation() as $row) {
            $model = ModelEventPersonAccommodation::createFromTableRow($row);
            $eventAcc = $model->getEventAccommodation();
            $items[] = [
                'label' => $model->getLabel(),
                'price' => self::getPriceFromModel($eventAcc, $price),
            ];
        }
        return $items;
    }

    /**
     * @param ModelEventAccommodation $modelEventAccommodation
     * @param Price $price
     * @return Price
     * @throws UnsupportedCurrencyException
     */
    private static function getPriceFromModel(ModelEventAccommodation $modelEventAccommodation, Price &$price): Price {
        switch ($price->getCurrency()) {
            case Price::CURRENCY_KC:
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
