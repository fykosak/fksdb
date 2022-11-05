<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\TransitionsGenerator;

use FKSDB\Models\Authorization\EventAuthorizator;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\PaymentState;
use FKSDB\Models\ORM\Services\Schedule\PersonScheduleService;
use FKSDB\Models\Transitions\Holder\PaymentHolder;
use FKSDB\Models\Transitions\Machine\Machine;
use FKSDB\Models\Transitions\Machine\PaymentMachine;
use FKSDB\Models\Transitions\Transition\UnavailableTransitionsException;
use FKSDB\Models\Transitions\TransitionsDecorator;
use Tracy\Debugger;

abstract class PaymentTransitions implements TransitionsDecorator
{

    protected EventAuthorizator $eventAuthorizator;
    protected PersonScheduleService $personScheduleService;

    public function __construct(
        EventAuthorizator $eventAuthorizator,
        PersonScheduleService $personScheduleService
    ) {
        $this->eventAuthorizator = $eventAuthorizator;
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

        $this->decorateTransitionAllToCanceled($machine);
        $this->decorateTransitionWaitingToReceived($machine);
    }

    abstract protected function getDatesCondition(): callable;

    /**
     * @throws UnavailableTransitionsException
     */
    private function decorateTransitionAllToCanceled(PaymentMachine $machine): void
    {
        foreach ([PaymentState::tryFrom(PaymentState::NEW), PaymentState::tryFrom(PaymentState::WAITING)] as $state) {
            $transition = $machine->getTransitionByStates($state, PaymentState::tryFrom(PaymentState::CANCELED));
            $transition->setCondition(fn() => true);
            $transition->beforeExecute[] = $this->getClosureDeleteRows();
            $transition->beforeExecute[] =
                fn(PaymentHolder $holder) => $holder->getModel()->update(['price' => null]);
        }
    }

    /**
     * @throws UnavailableTransitionsException
     */
    private function decorateTransitionWaitingToReceived(PaymentMachine $machine): void
    {
        $transition = $machine->getTransitionByStates(
            PaymentState::tryFrom(PaymentState::WAITING),
            PaymentState::tryFrom(PaymentState::RECEIVED)
        );
        $transition->beforeExecute[] = function (PaymentHolder $holder) {
            foreach ($holder->getModel()->getRelatedPersonSchedule() as $personSchedule) {
                $this->personScheduleService->storeModel([$personSchedule->state => 'received'], $personSchedule);
            }
        };
        $transition->setCondition(fn() => false);
    }

    private function getClosureDeleteRows(): callable
    {
        return function (PaymentHolder $holder) {
            Debugger::log('payment-deleted--' . \json_encode($holder->getModel()->toArray()), 'payment-info');
            foreach ($holder->getModel()->getSchedulePayment() as $row) {
                Debugger::log('payment-row-deleted--' . \json_encode($row->toArray()), 'payment-info');
                $row->delete();
            }
        };
    }
}
