<?php

namespace FKSDB\Payment\PriceCalculator\PreProcess;

use FKSDB\ORM\Models\ModelEventParticipant;
use FKSDB\ORM\Models\ModelPayment;
use FKSDB\ORM\Services\ServiceEventParticipant;
use FKSDB\Payment\PriceCalculator\Price;
use Nette\NotImplementedException;

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
     * @param \FKSDB\ORM\Models\ModelPayment $modelPayment
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
     * @param \FKSDB\ORM\Models\ModelPayment $modelPayment
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

    /**
     * @param \FKSDB\ORM\Models\ModelEventParticipant $modelEventAccommodation
     * @param Price $price
     * @return Price
     */
    private function getPriceFromModel(ModelEventParticipant $modelEventAccommodation, Price $price): Price {
        switch ($price->getCurrency()) {
            case Price::CURRENCY_KC:
                $amount = $modelEventAccommodation->price;
                break;
            default:
                throw new NotImplementedException(\sprintf(_('Mena %s nieje implentovaná'), $price->getCurrency()), 501);
        }
        return new Price($amount, $price->getCurrency());
    }
}
