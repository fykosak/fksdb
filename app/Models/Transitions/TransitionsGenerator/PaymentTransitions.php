<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\TransitionsGenerator;

use FKSDB\Models\Authorization\EventAuthorizator;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\ModelPayment;
use FKSDB\Models\ORM\Services\Schedule\ServicePersonSchedule;
use FKSDB\Models\Payment\Transition\PaymentMachine;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Machine\AbstractMachine;
use FKSDB\Models\Transitions\TransitionsDecorator;
use FKSDB\Models\Transitions\Machine\Machine;
use FKSDB\Models\Transitions\Transition\Statements\Conditions\ExplicitEventRole;
use FKSDB\Models\Transitions\Transition\Transition;
use FKSDB\Models\Transitions\Transition\UnavailableTransitionsException;
use Tracy\Debugger;

abstract class PaymentTransitions implements TransitionsDecorator {

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
    public function decorate(Machine $machine): void {
        if (!$machine instanceof PaymentMachine) {
            throw new BadTypeException(PaymentMachine::class, $machine);
        }
        $machine->setImplicitCondition(new ExplicitEventRole($this->eventAuthorizator, 'org', $machine->getEvent(), ModelPayment::RESOURCE_ID));

        $this->decorateTransitionInitToNew($machine);
        $this->decorateTransitionNewToWaiting($machine);
        $this->decorateTransitionAllToCanceled($machine);
        $this->decorateTransitionWaitingToReceived($machine);
    }

    /**
     * implicit transition when creating model (it's not executed only try condition!)
     * @throws \Exception
     */
    private function decorateTransitionInitToNew(PaymentMachine $machine): void {
        $transition = $machine->getTransitionById(Transition::createId(AbstractMachine::STATE_INIT, ModelPayment::STATE_NEW));
        $transition->setCondition($this->getDatesCondition());
    }

    /**
     * @throws \Exception
     */
    private function decorateTransitionNewToWaiting(PaymentMachine $machine): void {
        $transition = $machine->getTransitionById(Transition::createId(ModelPayment::STATE_NEW, ModelPayment::STATE_WAITING));
        $transition->setCondition($this->getDatesCondition());
    }

    abstract protected function getDatesCondition(): callable;

    /**
     * @throws UnavailableTransitionsException
     */
    private function decorateTransitionAllToCanceled(PaymentMachine $machine): void {
        foreach ([ModelPayment::STATE_NEW, ModelPayment::STATE_WAITING] as $state) {
            $transition = $machine->getTransitionById(Transition::createId($state, ModelPayment::STATE_CANCELED));
            $transition->setCondition(true);
            $transition->beforeExecuteCallbacks[] = $this->getClosureDeleteRows();
            $transition->beforeExecuteCallbacks[] = function (ModelHolder $holder) {
                $holder->getModel()->update(['price' => null]);
            };
        }
    }

    /**
     * @throws UnavailableTransitionsException
     */
    private function decorateTransitionWaitingToReceived(PaymentMachine $machine): void {
        $transition = $machine->getTransitionById(Transition::createId(ModelPayment::STATE_WAITING, ModelPayment::STATE_RECEIVED));
        $transition->beforeExecuteCallbacks[] = function (ModelHolder $holder) {
            foreach ($holder->getModel()->getRelatedPersonSchedule() as $personSchedule) {
                $this->servicePersonSchedule->updateModel($personSchedule, [$personSchedule->state => 'received']);
            }
        };
        $transition->setCondition(false);
    }

    private function getClosureDeleteRows(): callable {
        return function (ModelHolder $holder) {
            Debugger::log('payment-deleted--' . \json_encode($holder->getModel()->toArray()), 'payment-info');
            foreach ($holder->getModel()->related(DbNames::TAB_SCHEDULE_PAYMENT, 'payment_id') as $row) {
                Debugger::log('payment-row-deleted--' . \json_encode($row->toArray()), 'payment-info');
                $row->delete();
            }
        };
    }
}
