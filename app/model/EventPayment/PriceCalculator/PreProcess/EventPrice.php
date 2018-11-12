<?php

namespace FKSDB\EventPayment\PriceCalculator\PreProcess;

use FKSDB\ORM\ModelEvent;
use FKSDB\ORM\ModelEventParticipant;

class EventPrice extends AbstractPreProcess {
    /**
     * @var \ServiceEventParticipant
     */
    private $serviceEventParticipant;

    public function __construct(\ServiceEventParticipant $serviceEventParticipant) {
        $this->serviceEventParticipant = $serviceEventParticipant;
    }

    public function calculate(array $data, ModelEvent $event) {
        $ids = $this->getData($data);
        foreach ($ids as $id) {
            $row = $this->serviceEventParticipant->findByPrimary($id);
            $model = ModelEventParticipant::createFromTableRow($row);
            $this->price['kc'] += $model->price;
        }
    }

    protected function getData(array $data) {
        return $data['event_participants'];
    }

    public function getGridItems(array $data, ModelEvent $event): array {
        $items = [];
        $ids = $this->getData($data);
        foreach ($ids as $id) {
            $row = $this->serviceEventParticipant->findByPrimary($id);
            $model = ModelEventParticipant::createFromTableRow($row);
            $items[] = [
                'kc' => $model->price,
                'eur' => 'N/A',
                'label' => '',// TODO
            ];
        }
        return $items;
    }
}
