<?php

namespace FKSDB\Payment\Transition\Transitions;

use FKSDB\Authorization\EventAuthorizator;
use Closure;
use Exception;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelPayment;
use FKSDB\ORM\Services\ServiceEmailMessage;
use FKSDB\ORM\Services\ServicePayment;
use FKSDB\Payment\Transition\PaymentMachine;
use FKSDB\Transitions\AbstractTransitionsGenerator;
use FKSDB\Transitions\IStateModel;
use FKSDB\Transitions\Machine;
use FKSDB\Transitions\Statements\Conditions\DateBetween;
use FKSDB\Transitions\Statements\Conditions\ExplicitEventRole;
use FKSDB\Transitions\Transition;
use FKSDB\Mail\MailTemplateFactory;
use Nette\Database\Connection;
use Tracy\Debugger;

/**
 * Class Fyziklani14Payment
 * @author Michal Červeňák <miso@fykos.cz>
 */
class Fyziklani14Payment extends AbstractTransitionsGenerator {

    private Connection $connection;

    private ServicePayment $servicePayment;

    private EventAuthorizator $eventAuthorizator;

    private ServiceEmailMessage $serviceEmailMessage;

    private MailTemplateFactory $mailTemplateFactory;

    /**
     * Fyziklani13Payment constructor.
     * @param ServicePayment $servicePayment
     * @param Connection $connection
     * @param EventAuthorizator $eventAuthorizator
     * @param ServiceEmailMessage $serviceEmailMessage
     * @param MailTemplateFactory $mailTemplateFactory
     */
    public function __construct(
        ServicePayment $servicePayment,
        Connection $connection,
        EventAuthorizator $eventAuthorizator,
        ServiceEmailMessage $serviceEmailMessage,
        MailTemplateFactory $mailTemplateFactory
    ) {
        $this->connection = $connection;
        $this->servicePayment = $servicePayment;
        $this->eventAuthorizator = $eventAuthorizator;
        $this->serviceEmailMessage = $serviceEmailMessage;
        $this->mailTemplateFactory = $mailTemplateFactory;
    }

    /**
     * @param Machine $machine
     * @return void
     * @throws BadTypeException
     * @throws Exception
     */
    public function createTransitions(Machine $machine): void {
        if (!$machine instanceof PaymentMachine) {
            throw new BadTypeException(PaymentMachine::class, $machine);
        }
        $machine->setExplicitCondition(new ExplicitEventRole($this->eventAuthorizator, 'org', $machine->getEvent(), ModelPayment::RESOURCE_ID));
        $this->addTransitionInitToNew($machine);
        $this->addTransitionNewToWaiting($machine);
        $this->addTransitionAllToCanceled($machine);
        $this->addTransitionWaitingToReceived($machine);
    }

    /**
     * implicit transition when creating model (it's not executed only try condition!)
     * @param PaymentMachine $machine
     * @throws Exception
     */
    private function addTransitionInitToNew(PaymentMachine $machine): void {
        $transition = new Transition(Machine::STATE_INIT, ModelPayment::STATE_NEW, _('Create'));
        $transition->setCondition($this->getDatesCondition());
        $machine->addTransition($transition);
    }

    /**
     * @param PaymentMachine $machine
     * @return void
     * @throws Exception
     */
    private function addTransitionNewToWaiting(PaymentMachine $machine): void {
        $transition = new Transition(ModelPayment::STATE_NEW, ModelPayment::STATE_WAITING, _('Confirm payment'));

        $transition->setType(Transition::TYPE_SUCCESS);
        $transition->setCondition($this->getDatesCondition());

        $transition->beforeExecuteCallbacks[] = $machine->getSymbolGenerator();
        $transition->beforeExecuteCallbacks[] = $machine->getPriceCalculator();
        /**
         * @param IStateModel|ModelPayment $model
         */
        $transition->afterExecuteCallbacks[] = function (IStateModel $model = null) {
            $data = $this->emailData;
            $data['subject'] = \sprintf(_('Payment #%s was created'), $model->getPaymentId());
            $data['recipient'] = $model->getPerson()->getInfo()->email;
            $data['text'] = (string)$this->mailTemplateFactory->createWithParameters(
                'fyziklani/fyziklani2020/payment/create',
                $model->getPerson()->getPreferredLang(),
                ['model' => $model]
            );
            $this->serviceEmailMessage->addMessageToSend($data);
        };

        $machine->addTransition($transition);
    }

    /**
     * @return callable
     * @throws Exception
     */
    private function getDatesCondition(): callable {
        return new DateBetween('2020-01-01', '2020-02-13');
    }

    private function addTransitionAllToCanceled(PaymentMachine $machine): void {
        foreach ([ModelPayment::STATE_NEW, ModelPayment::STATE_WAITING] as $state) {

            $transition = new Transition($state, ModelPayment::STATE_CANCELED, _('Cancel payment'));
            $transition->setType(Transition::TYPE_DANGER);
            $transition->setCondition(function (): bool {
                return true;
            });
            $transition->beforeExecuteCallbacks[] = $this->getClosureDeleteRows();
            $transition->beforeExecuteCallbacks[] = function (ModelPayment $modelPayment) {
                $modelPayment->update(['price' => null]);
            };
            $machine->addTransition($transition);
        }
    }

    private function addTransitionWaitingToReceived(PaymentMachine $machine): void {
        $transition = new Transition(ModelPayment::STATE_WAITING, ModelPayment::STATE_RECEIVED, _('Paid'));
        $transition->beforeExecuteCallbacks[] = function (ModelPayment $modelPayment) {
            foreach ($modelPayment->getRelatedPersonSchedule() as $personSchedule) {
                $personSchedule->updateState('received');
            }
        };
        /**
         * @param IStateModel|ModelPayment $model
         */
        $transition->afterExecuteCallbacks[] = function (IStateModel $model = null) {
            $data = $this->emailData;
            $data['subject'] = \sprintf(_('We are receive payment #%s'), $model->getPaymentId());
            $data['recipient'] = $model->getPerson()->getInfo()->email;
            $data['text'] = (string)$this->mailTemplateFactory->createWithParameters(
                'fyziklani/fyziklani2020/payment/receive',
                $model->getPerson()->getPreferredLang(),
                ['model' => $model]
            );
            $this->serviceEmailMessage->addMessageToSend($data);
        };

        $transition->setCondition(function (): bool {
            return false;
        });
        $transition->setType(Transition::TYPE_SUCCESS);
        $machine->addTransition($transition);
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
