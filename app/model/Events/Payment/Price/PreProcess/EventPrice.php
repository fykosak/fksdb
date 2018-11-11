<?php


namespace FKSDB\Models\Payment\Price\PreProcess;


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

    public function run(array $data, ModelEvent $event) {
        $ids = $data['event_participants'];
        foreach ($ids as $id) {
            $row = $this->serviceEventParticipant->findByPrimary($id);
            $model = ModelEventParticipant::createFromTableRow($row);
            $this->price['kc'] += $model->price;
        }
    }
}
