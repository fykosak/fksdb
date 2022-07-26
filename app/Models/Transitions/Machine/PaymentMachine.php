<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Machine;

use FKSDB\Models\ORM\Models\PaymentState;
use FKSDB\Models\Transitions\Holder\PaymentHolder;
use Fykosak\NetteORM\Model;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\ORM\Services\ServiceEvent;
use FKSDB\Models\ORM\Services\ServicePayment;
use FKSDB\Models\Transitions\TransitionsDecorator;
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

    /**
     * @param PaymentModel|null $model
     */
    public function createHolder(?Model $model): PaymentHolder
    {
        return new PaymentHolder($model, $this->servicePayment);
    }
}
