<?php

namespace FKSDB\Payment\Transition\Transitions;

use Authorization\EventAuthorizator;
use Closure;
use Exception;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelPayment;
use FKSDB\ORM\Services\ServicePayment;
use FKSDB\Payment\Transition\PaymentMachine;
use FKSDB\Transitions\AbstractTransitionsGenerator;
use FKSDB\Transitions\IStateModel;
use FKSDB\Transitions\Machine;
use FKSDB\Transitions\Statements\Conditions\DateBetween;
use FKSDB\Transitions\Statements\Conditions\ExplicitEventRole;
use FKSDB\Transitions\Transition;
use FKSDB\Transitions\TransitionsFactory;
use Nette\Application\BadRequestException;
use Nette\Database\Connection;
use Nette\Localization\ITranslator;
use Nette\Mail\Message;
use Tracy\Debugger;
use function get_class;
use function json_encode;
use function sprintf;

/**
 * Class Fyziklani13Payment
 * @package FKSDB\Payment\Transition\Transitions
 */
class Fyziklani14Payment extends AbstractTransitionsGenerator {
    const EMAIL_BCC = 'fyziklani@fykos.cz';
    const EMAIL_FROM = 'Fyziklání <fyziklani@fykos.cz>';

    /**
     * @var Connection
     */
    private $connection;
    /**
     * @var ServicePayment
     */
    private $servicePayment;
    /**
     * @var EventAuthorizator
     */
    private $eventAuthorizator;
    /**
     * @var ITranslator
     */
    private $translator;

    /**
     * Fyziklani13Payment constructor.
     * @param ServicePayment $servicePayment
     * @param Connection $connection
     * @param TransitionsFactory $transitionFactory
     * @param EventAuthorizator $eventAuthorizator
     * @param ITranslator $translator
     */
    public function __construct(
        ServicePayment $servicePayment,
        Connection $connection,
        TransitionsFactory $transitionFactory,
        EventAuthorizator $eventAuthorizator,
        ITranslator $translator
    ) {
        parent::__construct($transitionFactory);
        $this->connection = $connection;
        $this->servicePayment = $servicePayment;
        $this->eventAuthorizator = $eventAuthorizator;
        $this->translator = $translator;
    }

    /**
     * @param Machine $machine
     * @throws BadRequestException
     * @throws Exception
     */
    public function createTransitions(Machine &$machine) {
        if (!$machine instanceof PaymentMachine) {
            throw new BadRequestException(sprintf(_('Expected class %s, got %s'), PaymentMachine::class, get_class($machine)));
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
    private function addTransitionInitToNew(PaymentMachine &$machine) {
        $transition = new Transition(Machine::STATE_INIT, ModelPayment::STATE_NEW, _('Create'));
        $transition->setCondition($this->getDatesCondition());
        $machine->addTransition($transition);
    }

    /**
     * @param PaymentMachine $machine
     * @throws Exception
     */
    private function addTransitionNewToWaiting(PaymentMachine &$machine) {
        $transition = new Transition(ModelPayment::STATE_NEW, ModelPayment::STATE_WAITING, _('Confirm payment'));

        $transition->setType(Transition::TYPE_SUCCESS);
        $transition->setCondition($this->getDatesCondition());

        $transition->beforeExecuteCallbacks[] = $machine->getSymbolGenerator();
        $transition->beforeExecuteCallbacks[] = $machine->getPriceCalculator();

        $transition->afterExecuteCallbacks[] = $this->transitionFactory->createMailCallback('fyziklani/fyziklani2020/payment/create',
            $this->getMailSetupCallback(_('Payment #%s was created'))
        );

        $machine->addTransition($transition);
    }

    /**
     * @return callable
     * @throws Exception
     */
    private function getDatesCondition(): callable {
        return new DateBetween('2020-01-01', '2020-02-13');
    }

    /**
     * @param string $subject
     * @return Closure
     */
    private function getMailSetupCallback(string $subject): Closure {
        return function (IStateModel $model) use ($subject): Message {
            $message = new Message();
            if ($model instanceof ModelPayment) {
                $message->setSubject(sprintf(_($subject), $model->getPaymentId()));
                $message->addTo($model->getPerson()->getInfo()->email);
            }
            $message->setFrom(self::EMAIL_FROM);
            $message->addBcc(self::EMAIL_BCC);
            return $message;
        };
    }

    /**
     * @param PaymentMachine $machine
     */
    private function addTransitionAllToCanceled(PaymentMachine &$machine) {
        foreach ([ModelPayment::STATE_NEW, ModelPayment::STATE_WAITING] as $state) {

            $transition = new Transition($state, ModelPayment::STATE_CANCELED, _('Cancel payment'));
            $transition->setType(Transition::TYPE_DANGER);
            $transition->setCondition(function () {
                return true;
            });
            $transition->beforeExecuteCallbacks[] = $this->getClosureDeleteRows();
            $transition->beforeExecuteCallbacks[] = function (ModelPayment $modelPayment) {
                $modelPayment->update(['price' => null]);
            };
            $machine->addTransition($transition);
        }
    }

    /**
     * @param PaymentMachine $machine
     */
    private function addTransitionWaitingToReceived(PaymentMachine &$machine) {
        $transition = new Transition(ModelPayment::STATE_WAITING, ModelPayment::STATE_RECEIVED, _('Paid'));
        $transition->beforeExecuteCallbacks[] = function (ModelPayment $modelPayment) {
            foreach ($modelPayment->getRelatedPersonSchedule() as $personSchedule) {
                $personSchedule->updateState('received');
            }
        };
        $transition->afterExecuteCallbacks[] = $this->transitionFactory->createMailCallback('fyziklani/fyziklani2020/payment/receive',
            $this->getMailSetupCallback(_('We are receive payment #%s')));

        $transition->setCondition(function () {
            return false;
        });
        $transition->setType(Transition::TYPE_SUCCESS);
        $machine->addTransition($transition);
    }

    /**
     * @return Closure
     */
    private function getClosureDeleteRows(): Closure {
        return function (ModelPayment $modelPayment) {
            Debugger::log('payment-deleted--' . json_encode($modelPayment->toArray()), 'payment-info');
            foreach ($modelPayment->related(DbNames::TAB_SCHEDULE_PAYMENT, 'payment_id') as $row) {
                Debugger::log('payment-row-deleted--' . json_encode($row->toArray()), 'payment-info');
                $row->delete();
            }
        };
    }
}
