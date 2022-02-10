<?php

declare(strict_types=1);

namespace FKSDB\Models\Payment\Transition;

use Fykosak\NetteORM\AbstractModel;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Models\ModelPayment;
use FKSDB\Models\ORM\Services\ServiceEvent;
use FKSDB\Models\ORM\Services\ServicePayment;
use FKSDB\Models\Transitions\TransitionsDecorator;
use FKSDB\Models\Transitions\Machine\Machine;
use Nette\Database\Explorer;

class PaymentMachine extends Machine
{
    public ModelEvent $event;
    private ServiceEvent $serviceEvent;
    public array $scheduleGroupTypes;
    private ServicePayment $servicePayment;

    public function __construct(Explorer $explorer, ServicePayment $servicePayment, ServiceEvent $serviceEvent)
    {
        parent::__construct($explorer);
        $this->serviceEvent = $serviceEvent;
        $this->servicePayment = $servicePayment;
    }

    final public function decorateTransitions(TransitionsDecorator $decorator): void
    {
        $decorator->decorate($this);
    }

    final public function setEventId(int $eventId): void
    {
        $event = $this->serviceEvent->findByPrimary($eventId);
        if (!is_null($event)) {
            $this->event = $event;
        }
    }

    final public function setScheduleGroupTypes(array $types): void
    {
        $this->scheduleGroupTypes = $types;
    }

    public function getCreatingState(): string
    {
        return ModelPayment::STATE_NEW;
    }

    /**
     * @param ModelPayment|null $model
     */
    public function createHolder(?AbstractModel $model): PaymentHolder
    {
        return new PaymentHolder($model, $this->servicePayment);
    }
}
