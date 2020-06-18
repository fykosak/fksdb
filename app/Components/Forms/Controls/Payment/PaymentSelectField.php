<?php

namespace FKSDB\Components\Forms\Controls\Payment;

use Exception;
use FKSDB\Components\React\ReactField;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\Schedule\ModelPersonSchedule;
use FKSDB\ORM\Services\Schedule\ServicePersonSchedule;
use Nette\Forms\Controls\TextInput;
use Nette\Utils\JsonException;

/**
 * Class PaymentSelectField
 * *
 */
class PaymentSelectField extends TextInput {

    use ReactField;
    /**
     * @var ServicePersonSchedule
     */
    private $servicePersonSchedule;
    /**
     * @var ModelEvent
     */
    private $event;
    /**
     * @var string
     */
    private $groupTypes;
    /**
     * @var bool
     */
    private $showAll;

    /**
     * PaymentSelectField constructor.
     * @param ServicePersonSchedule $servicePersonSchedule
     * @param ModelEvent $event
     * @param string[] $groupTypes
     * @param bool $showAll
     * @throws JsonException
     */
    public function __construct(ServicePersonSchedule $servicePersonSchedule, ModelEvent $event, array $groupTypes, bool $showAll = true) {
        parent::__construct();
        $this->servicePersonSchedule = $servicePersonSchedule;
        $this->event = $event;
        $this->groupTypes = $groupTypes;
        $this->showAll = $showAll;
        $this->appendProperty();
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getData(): string {
        $query = $this->servicePersonSchedule->getTable()->where('schedule_item.schedule_group.event_id', $this->event->event_id);
        if (count($this->groupTypes)) {
            $query->where('schedule_item.schedule_group.schedule_group_type IN', $this->groupTypes);
        }
        $items = [];
        /** @var ModelPersonSchedule $model */
        foreach ($query as $model) {
            $model->getPayment();
            if ($this->showAll || !$model->hasActivePayment()) {
                $items[] = [
                    'hasPayment' => false, //$model->hasActivePayment(),
                    'label' => $model->getLabel(),
                    'id' => $model->person_schedule_id,
                    'scheduleItem' => $model->getScheduleItem()->__toArray(),
                    'personId' => $model->person_id,
                    'personName' => $model->getPerson()->getFullName(),
                    'personFamilyName' => $model->getPerson()->family_name,
                ];
            }
        }
        return \json_encode($items);
    }

    protected function getReactId(): string {
        return 'payment.schedule-select';
    }
}
