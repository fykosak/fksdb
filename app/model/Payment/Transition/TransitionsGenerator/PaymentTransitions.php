<?php

namespace FKSDB\Payment\Transition\Transitions;

use FKSDB\Authorization\EventAuthorizator;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelPayment;
use FKSDB\ORM\Services\ServicePayment;
use FKSDB\Payment\Transition\PaymentMachine;
use FKSDB\Transitions\ITransitionsDecorator;
use FKSDB\Transitions\Machine;
use FKSDB\Transitions\Statements\Conditions\ExplicitEventRole;
use FKSDB\Transitions\Transition;
use Nette\Database\Connection;
use Nette\DI\Container;
use Tracy\Debugger;

/**
 * Class PaymentTransitions
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class PaymentTransitions implements ITransitionsDecorator {

    protected Connection $connection;

    protected ServicePayment $servicePayment;

    protected EventAuthorizator $eventAuthorizator;

    protected Container $container;

    /**
     * Fyziklani13Payment constructor.
     * @param Container $container
     * @param ServicePayment $servicePayment
     * @param Connection $connection
     * @param EventAuthorizator $eventAuthorizator
     */
    public function __construct(
        Container $container,
        ServicePayment $servicePayment,
        Connection $connection,
        EventAuthorizator $eventAuthorizator
    ) {
        $this->container = $container;
        $this->connection = $connection;
        $this->servicePayment = $servicePayment;
        $this->eventAuthorizator = $eventAuthorizator;
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
        $machine->setExplicitCondition(new ExplicitEventRole($this->eventAuthorizator, 'org', $machine->getEvent(), ModelPayment::RESOURCE_ID));

        $this->addTransitionInitToNew($machine);
        $this->addTransitionNewToWaiting($machine);
        $this->addTransitionAllToCanceled($machine);
        $this->addTransitionWaitingToReceived($machine);
        Debugger::barDump($machine, 'M');
    }
    /**
     * @param Machine $machine
     * @return void
     * @throws BadTypeException
     * @throws \Exception
     */
    /*  public function createTransitions(Machine $machine): void {
          if (!$machine instanceof PaymentMachine) {
              throw new BadTypeException(PaymentMachine::class, $machine);
          }
          $machine->setExplicitCondition(new ExplicitEventRole($this->eventAuthorizator, 'org', $machine->getEvent(), ModelPayment::RESOURCE_ID));

          $this->addTransitionInitToNew($machine);
          $this->addTransitionNewToWaiting($machine);
          $this->addTransitionAllToCanceled($machine);
          $this->addTransitionWaitingToReceived($machine);
          Debugger::barDump($machine);
      }*/

    /**
     * implicit transition when creating model (it's not executed only try condition!)
     * @param PaymentMachine $machine
     * @throws \Exception
     */
    private function addTransitionInitToNew(PaymentMachine $machine): void {
        $transition = $this->getTransition(Machine::STATE_INIT, ModelPayment::STATE_NEW);
        $transition->setCondition($this->getDatesCondition());
    }

    /**
     * @param PaymentMachine $machine
     * @return void
     * @throws \Exception
     */
    private function addTransitionNewToWaiting(PaymentMachine $machine): void {
        $transition = $this->getTransition(ModelPayment::STATE_NEW, ModelPayment::STATE_WAITING);
        $transition->setCondition($this->getDatesCondition());
    }

    abstract protected function getDatesCondition(): callable;

    private function addTransitionAllToCanceled(PaymentMachine $machine): void {

        foreach ([ModelPayment::STATE_NEW, ModelPayment::STATE_WAITING] as $state) {

            $transition = $this->getTransition($state, ModelPayment::STATE_CANCELED);
            $transition->setCondition(function (): bool {
                return true;
            });
            $transition->beforeExecuteCallbacks[] = $this->getClosureDeleteRows();
            $transition->beforeExecuteCallbacks[] = function (ModelPayment $modelPayment) {
                $modelPayment->update(['price' => null]);
            };
        }
    }

    private function addTransitionWaitingToReceived(PaymentMachine $machine): void {
        $transition = $this->getTransition(ModelPayment::STATE_WAITING, ModelPayment::STATE_RECEIVED);
        $transition->beforeExecuteCallbacks[] = function (ModelPayment $modelPayment) {
            foreach ($modelPayment->getRelatedPersonSchedule() as $personSchedule) {
                $personSchedule->updateState('received');
            }
        };
        $transition->setCondition(function (): bool {
            return false;
        });
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

    abstract protected function getEmailDirectory(): string;

    abstract protected function getMachinePrefix(): string;

    final protected function getTransition(string $source, string $target): Transition {
        $mask = 'transitions.%s.%s.%s';
        return $this->container->getService(sprintf($mask, $this->getMachinePrefix(), $source, $target));
    }
}
