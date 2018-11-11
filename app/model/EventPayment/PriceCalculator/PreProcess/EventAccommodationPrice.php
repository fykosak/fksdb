<?php

namespace FKSDB\EventPayment\PriceCalculator\PreProcess;

use FKSDB\ORM\ModelEvent;
use FKSDB\ORM\ModelEventPersonAccommodation;

class EventAccommodationPrice extends AbstractPreProcess {
    /**
     * @var \ServiceEventPersonAccommodation
     */
    private $serviceEventPersonAccommodation;

    public function __construct(\ServiceEventPersonAccommodation $serviceEventPersonAccommodation) {
        $this->serviceEventPersonAccommodation = $serviceEventPersonAccommodation;
    }

    public function run(array $data, ModelEvent $event) {
        $ids = $data['accommodated_person_ids'];
        foreach ($ids as $id) {
            $row = $this->serviceEventPersonAccommodation->findByPrimary($id);
            $model = ModelEventPersonAccommodation::createFromTableRow($row);
            $eventAcc = $model->getEventAccommodation();
            $this->price['kc'] += $eventAcc->price_kc;
            $this->price['eur'] += $eventAcc->price_eur;
        }
    }
}
