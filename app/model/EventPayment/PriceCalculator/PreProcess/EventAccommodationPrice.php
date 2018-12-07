<?php

namespace FKSDB\EventPayment\PriceCalculator\PreProcess;

use FKSDB\EventPayment\PriceCalculator\Price;
use FKSDB\ORM\ModelEvent;
use FKSDB\ORM\ModelEventAccommodation;
use FKSDB\ORM\ModelEventPersonAccommodation;
use Nette\NotImplementedException;

class EventAccommodationPrice extends AbstractPreProcess {
    /**
     * @var \ServiceEventPersonAccommodation
     */
    private $serviceEventPersonAccommodation;


    public function __construct(\ServiceEventPersonAccommodation $serviceEventPersonAccommodation) {
        $this->serviceEventPersonAccommodation = $serviceEventPersonAccommodation;
    }

    public function calculate(array $data, ModelEvent $event, $currency): Price {
        $price = new Price(0, $currency);
        $ids = $this->getData($data);
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

    public function getGridItems(array $data, ModelEvent $event, $currency): array {
        $price = new Price(0, $currency);
        $items = [];
        $ids = $this->getData($data);
        foreach ($ids as $id) {
            $row = $this->serviceEventPersonAccommodation->findByPrimary($id);
            $model = ModelEventPersonAccommodation::createFromTableRow($row);
            $eventAcc = $model->getEventAccommodation();
            $fromDate = $eventAcc->date->format('d. m.');
            $toDate = $eventAcc->date->add(new \DateInterval('P1D'))->format('d. m. Y');

            $items[] = [
                'label' => \sprintf(_('Ubytovaní pre osobu %s od %s do %s v hoteli %s'), $model->getPerson()->getFullName(), $fromDate, $toDate, $eventAcc->name),
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

    protected function getData(array $data) {
        return $data['accommodated_person_ids'];
    }
}
