<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\TransitionsGenerator;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\PaymentState;
use FKSDB\Models\ORM\Services\Schedule\PersonScheduleService;
use FKSDB\Models\Transitions\Holder\PaymentHolder;
use FKSDB\Models\Transitions\Machine\Machine;
use FKSDB\Models\Transitions\Machine\PaymentMachine;
use FKSDB\Models\Transitions\TransitionsDecorator;
use Tracy\Debugger;

class PaymentTransitions implements TransitionsDecorator
{
    protected PersonScheduleService $personScheduleService;

    public function __construct(
        PersonScheduleService $personScheduleService
    ) {
        $this->personScheduleService = $personScheduleService;
    }

    /**
     * @throws BadTypeException
     * @throws \Exception
     */
    public function decorate(Machine $machine): void
    {
        if (!$machine instanceof PaymentMachine) {
            throw new BadTypeException(PaymentMachine::class, $machine);
        }
        foreach (
            [
                PaymentState::InProgress,
                PaymentState::Waiting,
            ] as $state
        ) {
            $transition = $machine->getTransitionByStates($state, PaymentState::Canceled);
            $transition->beforeExecute[] = function (PaymentHolder $holder): void {
                Debugger::log('payment-deleted--' . \json_encode($holder->getModel()->toArray()), 'payment-info');
                foreach ($holder->getModel()->getSchedulePayment() as $row) {
                    Debugger::log('payment-row-deleted--' . \json_encode($row->toArray()), 'payment-info');
                    $row->delete();
                }
            };
            $transition->beforeExecute[] =
                fn(PaymentHolder $holder) => $holder->getModel()->update(['price' => null]);
        }
    }
}
