<?php

namespace FKSDB\Components\Forms\Controls\Payment;

use Exception;
use FKSDB\Components\React\ReactField;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\Schedule\ModelPersonSchedule;
use FKSDB\ORM\Services\Schedule\ServicePersonSchedule;
use Nette\Forms\Controls\TextInput;
use Nette\Utils\JsonException;
use function json_encode;

/**
 * Class PaymentSelectField
 * @package FKSDB\Components\Forms\Controls\Payment
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
    private $groupType;
    /**
     * @var bool
     */
    private $showAll = true;

    /**
     * PaymentSelectField constructor.
     * @param ServicePersonSchedule $servicePersonSchedule
     * @param ModelEvent $event
     * @param string $groupType
     * @param bool $showAll
     * @throws JsonException
     */
    public function __construct(ServicePersonSchedule $servicePersonSchedule, ModelEvent $event, string $groupType, bool $showAll = true) {
        parent::__construct();
        $this->servicePersonSchedule = $servicePersonSchedule;
        $this->event = $event;
        $this->groupType = $groupType;
        $this->showAll = $showAll;
        $this->appendProperty();
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getData(): string {
        $query = $this->servicePersonSchedule->where('schedule_item.schedule_group.event_id', $this->event->event_id);
        if ($this->groupType) {
            $query->where('schedule_item.schedule_group.schedule_group_type', $this->groupType);
        }
        $items = [];
        foreach ($query as $row) {
            $model = ModelPersonSchedule::createFromActiveRow($row);
            $model->getPayment();
            if ($this->showAll || !$model->hasActivePayment()) {
                $items[] = [
                    'hasPayment' => $model->hasActivePayment(),
                    'label' => $model->getLabel(),
                    'id' => $model->person_schedule_id,
                    'scheduleItem' => $model->getScheduleItem()->__toArray(),
                    'personId' => $model->person_id,
                    'personName' => $model->getPerson()->getFullName(),
                    'personFamilyName' => $model->getPerson()->family_name,
                ];
            }
        }
        return json_encode($items);
    }

    /**
     * @return string
     */
    protected function getReactId(): string {
        return 'payment.schedule-select';
    }
}
