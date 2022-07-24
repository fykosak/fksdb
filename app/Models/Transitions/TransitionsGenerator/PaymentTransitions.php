<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\TransitionsGenerator;

use FKSDB\Models\Authorization\EventAuthorizator;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\ORM\Models\PaymentState;
use FKSDB\Models\ORM\Services\Schedule\ServicePersonSchedule;
use FKSDB\Models\Transitions\Holder\PaymentHolder;
use FKSDB\Models\Transitions\Machine\PaymentMachine;
use FKSDB\Models\Transitions\Transition\Statements\Conditions\ExplicitEventRole;
use FKSDB\Models\Transitions\TransitionsDecorator;
use FKSDB\Models\Transitions\Machine\Machine;
use FKSDB\Models\Transitions\Transition\Transition;
use FKSDB\Models\Transitions\Transition\UnavailableTransitionsException;
use Tracy\Debugger;

abstract class PaymentTransitions implements TransitionsDecorator
{

    protected EventAuthorizator $eventAuthorizator;
    protected ServicePersonSchedule $servicePersonSchedule;

    public function __construct(
        EventAuthorizator $eventAuthorizator,
        ServicePersonSchedule $servicePersonSchedule
    ) {
        $this->eventAuthorizator = $eventAuthorizator;
        $this->servicePersonSchedule = $servicePersonSchedule;
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
        $machine->setImplicitCondition(
            new ExplicitEventRole($this->eventAuthorizator, 'org', $machine->event, PaymentModel::RESOURCE_ID)
        );

        $this->decorateTransitionAllToCanceled($machine);
        $this->decorateTransitionWaitingToReceived($machine);
    }

    abstract protected function getDatesCondition(): callable;

    /**
     * @throws UnavailableTransitionsException
     */
    private function decorateTransitionAllToCanceled(PaymentMachine $machine): void
    {
        foreach ([PaymentState::NEW, PaymentState::WAITING] as $state) {
            $transition = $machine->getTransitionById(Transition::createId($state, PaymentState::CANCELED));
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
        $transition = $machine->getTransitionById(
            Transition::createId(PaymentState::WAITING, PaymentState::RECEIVED)
        );
        $transition->beforeExecute[] = function (PaymentHolder $holder) {
            foreach ($holder->getModel()->getRelatedPersonSchedule() as $personSchedule) {
                $this->servicePersonSchedule->updateModel($personSchedule, [$personSchedule->state => 'received']);
            }
        };
        $transition->setCondition(fn() => false);
    }

    private function getClosureDeleteRows(): callable
    {
        return function (PaymentHolder $holder) {
            Debugger::log('payment-deleted--' . \json_encode($holder->getModel()->toArray()), 'payment-info');
            foreach ($holder->getModel()->related(DbNames::TAB_SCHEDULE_PAYMENT, 'payment_id') as $row) {
                Debugger::log('payment-row-deleted--' . \json_encode($row->toArray()), 'payment-info');
                $row->delete();
            }
        };
    }
}
