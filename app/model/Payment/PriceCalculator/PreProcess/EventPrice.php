<?php

namespace FKSDB\Payment\PriceCalculator\PreProcess;

use FKSDB\ORM\Models\ModelPayment;
use FKSDB\ORM\Services\ServiceEventParticipant;
use FKSDB\Payment\Price;

/**
 * Class EventPrice
 * *
 */
class EventPrice implements IPreprocess {
    /**
     * @var ServiceEventParticipant
     */
    private $serviceEventParticipant;

    /**
     * EventPrice constructor.
     * @param ServiceEventParticipant $serviceEventParticipant
     */
    public function __construct(ServiceEventParticipant $serviceEventParticipant) {
        $this->serviceEventParticipant = $serviceEventParticipant;
    }

    public static function calculate(ModelPayment $modelPayment): Price {
        /* $price = new Price(0, $modelPayment->currency);
         $ids = $this->getData($modelPayment);
         foreach ($ids as $id) {
             $row = $this->serviceEventParticipant->findByPrimary($id);
             $model = ModelEventParticipant::createFromActiveRow($row);
             $price->add($this->getPriceFromModel($model, $price));
         }*/
        return new Price(0, $modelPayment->currency);
    }

    public static function getGridItems(ModelPayment $modelPayment): array {
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
