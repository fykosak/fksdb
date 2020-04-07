<?php

namespace FKSDB\Payment\PriceCalculator\PreProcess;

use FKSDB\ORM\Models\ModelPayment;
use FKSDB\ORM\Services\ServiceEventParticipant;
use FKSDB\Payment\Price;

/**
 * Class EventPrice
 * @package FKSDB\Payment\PriceCalculator\PreProcess
 */
class EventPrice extends AbstractPreProcess {
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

    /**
     * @param ModelPayment $modelPayment
     * @return Price
     */
    public static function calculate(ModelPayment $modelPayment): Price {
        /* $price = new Price(0, $modelPayment->currency);
         $ids = $this->getData($modelPayment);
         foreach ($ids as $id) {
             $row = $this->serviceEventParticipant->findByPrimary($id);
             $model = ModelEventParticipant::createFromTableRow($row);
             $price->add($this->getPriceFromModel($model, $price));
         }*/
        return new Price(0, $modelPayment->currency);
    }

    /**
     * @param ModelPayment $modelPayment
     * @return array
     */
    public static function getGridItems(ModelPayment $modelPayment): array {
        /*$price = new Price(0, $modelPayment->currency);
        $items = [];
        $ids = $this->getData([]);
        foreach ($ids as $id) {
            $row = $this->serviceEventParticipant->findByPrimary($id);
            $model = ModelEventParticipant::createFromTableRow($row);
            $items[] = [
                'price' => $this->getPriceFromModel($model, $price),
                'label' => '',// TODO
            ];
        }
        return $items;*/
        return [];
    }
}
