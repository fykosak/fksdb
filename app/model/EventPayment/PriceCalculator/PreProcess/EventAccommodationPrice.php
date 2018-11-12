<?php

namespace FKSDB\EventPayment\PriceCalculator\PreProcess;

use FKSDB\ORM\ModelEvent;
use FKSDB\ORM\ModelEventAccommodation;
use FKSDB\ORM\ModelEventPersonAccommodation;

class EventAccommodationPrice extends AbstractPreProcess {
    /**
     * @var \ServiceEventPersonAccommodation
     */
    private $serviceEventPersonAccommodation;

    public function __construct(\ServiceEventPersonAccommodation $serviceEventPersonAccommodation) {
        $this->serviceEventPersonAccommodation = $serviceEventPersonAccommodation;
    }

    public function calculate(array $data, ModelEvent $event) {
        $ids = $this->getData($data);
        foreach ($ids as $id) {
            $eventAcc = $this->getAccommodation($id);
            $this->price['kc'] += $eventAcc->price_kc;
            $this->price['eur'] += $eventAcc->price_eur;
        }
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
                'label' => \sprintf(_('Ubytovaní pre osobu %s od %s do %s'), $model->getPerson()->getFullName(), $fromDate, $toDate),
                'kc' => $eventAcc->price_kc,
                'eur' => $eventAcc->price_eur,
            ];
        }
        return $items;
    }

    protected function getData(array $data) {
        return $data['accommodated_person_ids'];
    }

}
