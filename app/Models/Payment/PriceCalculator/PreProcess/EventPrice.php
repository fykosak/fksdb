<?php

declare(strict_types=1);

namespace FKSDB\Models\Payment\PriceCalculator\PreProcess;

use FKSDB\Models\ORM\Models\ModelPayment;
use Fykosak\Utils\Price\MultiCurrencyPrice;

class EventPrice implements Preprocess
{

    public static function calculate(ModelPayment $modelPayment): MultiCurrencyPrice
    {
        /* $price = new Price(0, $modelPayment->currency);
         $ids = $this->getData($modelPayment);
         foreach ($ids as $id) {
             $row = $this->serviceEventParticipant->findByPrimary($id);
             $model = ModelEventParticipant::createFromActiveRow($row);
             $price->add($this->getPriceFromModel($model, $price));
         }*/
        return new MultiCurrencyPrice([]);
    }

    public static function getGridItems(ModelPayment $modelPayment): array
    {
        /*$price = new Price(0, $modelPayment->currency);
        $items = [];
        $ids = $this->getData([]);
        foreach ($ids as $id) {
            $row = $this->serviceEventParticipant->findByPrimary($id);
            $model = ModelEventParticipant::createFromActiveRow($row);
            $items[] = [
                'price' => $this->getPriceFromModel($model, $price),
                'label' => '',// TODO
            ];
        }
        return $items;*/
        return [];
    }
}
