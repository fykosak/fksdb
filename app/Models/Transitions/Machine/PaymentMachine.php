<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Machine;

use FKSDB\Models\Transitions\Holder\PaymentHolder;
use Fykosak\NetteORM\Model;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\ORM\Services\EventService;
use FKSDB\Models\ORM\Services\PaymentService;
use FKSDB\Models\Transitions\TransitionsDecorator;
use Nette\Database\Explorer;

class PaymentMachine extends Machine
{
    public EventModel $event;
    private EventService $eventService;
    public array $scheduleGroupTypes;
    private PaymentService $paymentService;

    public function __construct(Explorer $explorer, PaymentService $paymentService, EventService $eventService)
    {
        parent::__construct($explorer);
        $this->eventService = $eventService;
        $this->paymentService = $paymentService;
    }

    final public function decorateTransitions(TransitionsDecorator $decorator): void
    {
        $decorator->decorate($this);
    }

    final public function setEventId(int $eventId): void
    {
        $event = $this->eventService->findByPrimary($eventId);
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
        return new PaymentHolder($model, $this->paymentService);
    }
}
