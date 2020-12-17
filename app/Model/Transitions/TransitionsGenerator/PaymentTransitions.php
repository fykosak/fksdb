<?php

namespace FKSDB\Model\Transitions\TransitionsGenerator;

use FKSDB\Model\Authorization\EventAuthorizator;
use FKSDB\Model\Exceptions\BadTypeException;
use FKSDB\Model\ORM\DbNames;
use FKSDB\Model\ORM\Models\ModelPayment;
use FKSDB\Model\ORM\Services\Schedule\ServicePersonSchedule;
use FKSDB\Model\Payment\Transition\PaymentMachine;
use FKSDB\model\Transitions\ITransitionsDecorator;
use FKSDB\Model\Transitions\Machine\Machine;
use FKSDB\Model\Transitions\Transition\Statements\Conditions\ExplicitEventRole;
use FKSDB\Model\Transitions\Transition\Transition;
use FKSDB\Model\Transitions\Transition\UnavailableTransitionsException;
use Tracy\Debugger;

/**
 * Class PaymentTransitions
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class PaymentTransitions implements ITransitionsDecorator {

    protected EventAuthorizator $eventAuthorizator;

    protected ServicePersonSchedule $servicePersonSchedule;

    /**
     * Fyziklani13Payment constructor.
     * @param EventAuthorizator $eventAuthorizator
     * @param ServicePersonSchedule $servicePersonSchedule
     */
    public function __construct(
        EventAuthorizator $eventAuthorizator,
        ServicePersonSchedule $servicePersonSchedule
    ) {
        $this->eventAuthorizator = $eventAuthorizator;
        $this->servicePersonSchedule = $servicePersonSchedule;
    }

    /**
     * @param Machine $machine
     * @return void
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
     * @param PaymentMachine $machine
     * @throws \Exception
     */
    private function decorateTransitionInitToNew(PaymentMachine $machine): void {
        $transition = $machine->getTransitionById(Transition::createId(Machine::STATE_INIT, ModelPayment::STATE_NEW));
        $transition->setCondition($this->getDatesCondition());
    }

    /**
     * @param PaymentMachine $machine
     * @return void
     * @throws \Exception
     */
    private function decorateTransitionNewToWaiting(PaymentMachine $machine): void {
        $transition = $machine->getTransitionById(Transition::createId(ModelPayment::STATE_NEW, ModelPayment::STATE_WAITING));
        $transition->setCondition($this->getDatesCondition());
    }

    abstract protected function getDatesCondition(): callable;

    /**
     * @param PaymentMachine $machine
     * @return void
     * @throws UnavailableTransitionsException
     */
    private function decorateTransitionAllToCanceled(PaymentMachine $machine): void {

        foreach ([ModelPayment::STATE_NEW, ModelPayment::STATE_WAITING] as $state) {

            $transition = $machine->getTransitionById(Transition::createId($state, ModelPayment::STATE_CANCELED));
            $transition->setCondition(true);
            $transition->beforeExecuteCallbacks[] = $this->getClosureDeleteRows();
            $transition->beforeExecuteCallbacks[] = function (ModelPayment $modelPayment) {
                $modelPayment->update(['price' => null]);
            };
        }
    }

    /**
     * @param PaymentMachine $machine
     * @return void
     * @throws UnavailableTransitionsException
     */
    private function decorateTransitionWaitingToReceived(PaymentMachine $machine): void {
        $transition = $machine->getTransitionById(Transition::createId(ModelPayment::STATE_WAITING, ModelPayment::STATE_RECEIVED));
        $transition->beforeExecuteCallbacks[] = function (ModelPayment $modelPayment) {
            foreach ($modelPayment->getRelatedPersonSchedule() as $personSchedule) {
                $this->servicePersonSchedule->updateModel2($personSchedule, [$personSchedule->getStateColumn() => 'received']);
            }
        };
        $transition->setCondition(false);
    }

    private function getClosureDeleteRows(): callable {
        return function (ModelPayment $modelPayment) {
            Debugger::log('payment-deleted--' . \json_encode($modelPayment->toArray()), 'payment-info');
            foreach ($modelPayment->related(DbNames::TAB_SCHEDULE_PAYMENT, 'payment_id') as $row) {
                Debugger::log('payment-row-deleted--' . \json_encode($row->toArray()), 'payment-info');
                $row->delete();
            }
        };
    }
}
