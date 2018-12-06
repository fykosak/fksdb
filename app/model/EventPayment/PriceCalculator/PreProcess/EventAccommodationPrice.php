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
        $this->price = new Price(0, $currency);
        $ids = $this->getData($data);
        foreach ($ids as $id) {
            $eventAcc = $this->getAccommodation($id);
            $price = $this->getPriceFromModel($eventAcc);
            $this->price->add($price);
        }
        return $this->price;
    }

    private function getAccommodation($id): ModelEventAccommodation {
        $row = $this->serviceEventPersonAccommodation->findByPrimary($id);
        $model = ModelEventPersonAccommodation::createFromTableRow($row);
        return $model->getEventAccommodation();
    }

    public function getGridItems(array $data, ModelEvent $event): array {
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
                'price' => $this->getPriceFromModel($eventAcc),
            ];
        }
        return $items;
    }

    private function getPriceFromModel(ModelEventAccommodation $modelEventAccommodation): Price {
        switch ($this->price->getCurrency()) {
            case Price::CURRENCY_KC:
                $amount = $modelEventAccommodation->price_kc;
                break;
            case Price::CURRENCY_EUR:
                $amount = $modelEventAccommodation->price_eur;
                break;
            default:
                throw new NotImplementedException(\sprintf(_('Mena %s nieje implentovaná'), $this->price->getCurrency()));
        }
        return new Price($amount, $this->price->getCurrency());
    }

    protected function getData(array $data) {
        return $data['accommodated_person_ids'];
    }

}
