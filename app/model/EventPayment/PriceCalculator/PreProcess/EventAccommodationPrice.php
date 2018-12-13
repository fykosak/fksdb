<?php

namespace FKSDB\EventPayment\PriceCalculator\PreProcess;

use FKSDB\EventPayment\PriceCalculator\Price;
use FKSDB\ORM\ModelEventAccommodation;
use FKSDB\ORM\ModelEventPersonAccommodation;
use FKSDB\ORM\ModelPayment;
use Nette\NotImplementedException;

class EventAccommodationPrice extends AbstractPreProcess {
    /**
     * @var \ServiceEventPersonAccommodation
     */
    private $serviceEventPersonAccommodation;


    public function __construct(\ServiceEventPersonAccommodation $serviceEventPersonAccommodation) {
        $this->serviceEventPersonAccommodation = $serviceEventPersonAccommodation;
    }

    public function calculate(ModelPayment $modelPayment): Price {
        $price = new Price(0, $modelPayment->currency);
        $ids = $this->getData($modelPayment);
        foreach ($ids as $id) {
            $eventAcc = $this->getAccommodation($id);
            $modelPrice = $this->getPriceFromModel($eventAcc, $price);
            $price->add($modelPrice);
        }
        return $price;
    }

    private function getAccommodation($id): ModelEventAccommodation {
        $row = $this->serviceEventPersonAccommodation->findByPrimary($id);
        $model = ModelEventPersonAccommodation::createFromTableRow($row);
        return $model->getEventAccommodation();
    }

    public function getGridItems(ModelPayment $modelPayment): array {
        $price = new Price(0, $modelPayment->currency);
        $items = [];

        foreach ($modelPayment->getRelatedPersonAccommodation() as $row) {
            $model = ModelEventPersonAccommodation::createFromTableRow($row);
            $eventAcc = $model->getEventAccommodation();
            $items[] = [
                'label' => $model->getLabel(),
                'price' => $this->getPriceFromModel($eventAcc, $price),
            ];
        }
        return $items;
    }

    private function getPriceFromModel(ModelEventAccommodation $modelEventAccommodation, Price &$price): Price {
        switch ($price->getCurrency()) {
            case Price::CURRENCY_KC:
                $amount = $modelEventAccommodation->price_kc;
                break;
            case Price::CURRENCY_EUR:
                $amount = $modelEventAccommodation->price_eur;
                break;
            default:
                throw new NotImplementedException(\sprintf(_('Mena %s nieje implentovaná'), $price->getCurrency()));
        }
        return new Price($amount, $price->getCurrency());
    }

    protected function getData(ModelPayment $modelPayment) {
        return $modelPayment->getRelatedPersonAccommodation();
    }
}
