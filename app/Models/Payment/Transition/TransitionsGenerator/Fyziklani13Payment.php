<?php

namespace FKSDB\Models\Payment\Transition\TransitionsGenerator;

use FKSDB\Models\Authorization\EventAuthorizator;
use Exception;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\ModelPayment;
use FKSDB\Models\ORM\Services\ServiceEmailMessage;
use FKSDB\Models\ORM\Services\ServicePayment;
use FKSDB\Models\Payment\Transition\PaymentMachine;
use FKSDB\Models\Transitions\AbstractTransitionsGenerator;
use FKSDB\Models\Transitions\StateModel;
use FKSDB\Models\Mail\MailTemplateFactory;
use FKSDB\Models\Transitions\Machine\Machine;
use FKSDB\Models\Transitions\Transition\Statements\Conditions\DateBetween;
use FKSDB\Models\Transitions\Transition\Statements\Conditions\ExplicitEventRole;
use FKSDB\Models\Transitions\Transition\Transition;
use Nette\Database\Connection;
use Tracy\Debugger;

/**
 * Class Fyziklani13Payment
 * @author Michal Červeňák <miso@fykos.cz>
 */
class Fyziklani13Payment extends AbstractTransitionsGenerator {

    private Connection $connection;

    private ServicePayment $servicePayment;

    private EventAuthorizator $eventAuthorizator;

    private ServiceEmailMessage $serviceEmailMessage;

    private MailTemplateFactory $mailTemplateFactory;

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
         * @param StateModel|ModelPayment|null $model
         */
        $transition->afterExecuteCallbacks[] = function (StateModel $model = null) {
            $data = $this->emailData;
            $data['subject'] = \sprintf(_('Payment #%s was created'), $model->getPaymentId());
            $data['recipient'] = $model->getPerson()->getInfo()->email;
            $data['text'] = (string)$this->mailTemplateFactory->createWithParameters(
                'fyziklani/fyziklani2019/payment/create',
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
        return new DateBetween('2019-01-21', '2019-02-15');
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
         * @param StateModel|ModelPayment|null $model
         */
        $transition->afterExecuteCallbacks[] = function (StateModel $model = null) {
            $data = $this->emailData;
            $data['subject'] = \sprintf(_('We are receive payment #%s'), $model->getPaymentId());
            $data['recipient'] = $model->getPerson()->getInfo()->email;
            $data['text'] = (string)$this->mailTemplateFactory->createWithParameters(
                'fyziklani/fyziklani2019/payment/receive',
                $model->getPerson()->getPreferredLang(),
                ['model' => $model]
            );
            $this->serviceEmailMessage->addMessageToSend($data);
        };

        $transition->setCondition(function () {
            return false;
        });
        $transition->setType(Transition::TYPE_SUCCESS);
        $machine->addTransition($transition);
    }

    private function getClosureDeleteRows(): callable {
        return function (ModelPayment $modelPayment): void {
            Debugger::log('payment-deleted--' . \json_encode($modelPayment->toArray()), 'payment-info');
            foreach ($modelPayment->related(DbNames::TAB_SCHEDULE_PAYMENT, 'payment_id') as $row) {
                Debugger::log('payment-row-deleted--' . \json_encode($row->toArray()), 'payment-info');
                $row->delete();
            }
        };
    }
}
