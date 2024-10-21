<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\TransitionsGenerator;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\PaymentState;
use FKSDB\Models\ORM\Models\Schedule\SchedulePaymentModel;
use FKSDB\Models\Transitions\Holder\PaymentHolder;
use FKSDB\Models\Transitions\Machine\Machine;
use FKSDB\Models\Transitions\Machine\PaymentMachine;
use FKSDB\Models\Transitions\TransitionsDecorator;
use Tracy\Debugger;

/**
 * @phpstan-implements TransitionsDecorator<PaymentHolder>
 */
final class PaymentTransitions implements TransitionsDecorator
{
    /**
     * @param PaymentMachine $machine
     * @throws \Exception
     * @throws BadTypeException
     */
    public function decorate(Machine $machine): void
    {
        if (!$machine instanceof PaymentMachine) {
            throw new BadTypeException(PaymentMachine::class, $machine);
        }
        foreach ([PaymentState::InProgress, PaymentState::Waiting] as $state) {
            $transition = $machine->getTransitions()
                ->filterBySource($state)
                ->filterByTarget(PaymentState::Canceled)
                ->select();
            $transition->beforeExecute[] = function (PaymentHolder $holder): void {
                Debugger::log('payment-deleted--' . \json_encode($holder->getModel()->toArray()), 'payment-info');
                /** @var SchedulePaymentModel $row */
                foreach ($holder->getModel()->getSchedulePayment() as $row) {
                    Debugger::log('payment-row-deleted--' . \json_encode($row->toArray()), 'payment-info');
                    $row->delete();
                }
            };
            $transition->beforeExecute[] =
                function (PaymentHolder $holder) {
                    $holder->getModel()->update(['price' => null]);
                };
        }
    }
}
