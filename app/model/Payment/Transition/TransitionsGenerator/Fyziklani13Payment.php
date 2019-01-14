<?php

namespace FKSDB\Payment\Transition\Transitions;

use FKSDB\ORM\ModelEvent;
use FKSDB\ORM\ModelEventPersonAccommodation;
use FKSDB\ORM\ModelPayment;
use FKSDB\Payment\PriceCalculator\PriceCalculatorFactory;
use FKSDB\Payment\SymbolGenerator\SymbolGeneratorFactory;
use FKSDB\Payment\Transition\PaymentMachine;
use FKSDB\Transitions\AbstractTransitionsGenerator;
use FKSDB\Transitions\IStateModel;
use FKSDB\Transitions\Machine;
use FKSDB\Transitions\Transition;
use FKSDB\Transitions\TransitionsFactory;
use Nette\Application\BadRequestException;
use Nette\Database\Connection;
use Nette\DateTime;
use Nette\Mail\Message;
use Nette\NotImplementedException;


class Fyziklani13Payment extends AbstractTransitionsGenerator {
    const EMAIL_BCC = 'fyziklani@fykos.cz';
    const EMAIL_FROM = 'fyziklani@fykos.cz';
    /**
     * @var SymbolGeneratorFactory
     */
    private $symbolGeneratorFactory;

    /**
     * @var PriceCalculatorFactory
     */
    private $priceCalculatorFactory;
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection, TransitionsFactory $transitionFactory, SymbolGeneratorFactory $symbolGeneratorFactory, PriceCalculatorFactory $priceCalculatorFactory) {
        parent::__construct($transitionFactory);
        $this->connection = $connection;
        $this->symbolGeneratorFactory = $symbolGeneratorFactory;
        $this->priceCalculatorFactory = $priceCalculatorFactory;
    }

    /**
     * @param Machine $machine
     * @throws BadRequestException
     */
    public function createTransitions(Machine &$machine) {
        if (!$machine instanceof PaymentMachine) {
            throw new BadRequestException(\sprintf(_('Expected class PaymentMachine, got %s'), \get_class($machine)));
        }

        $this->addTransitionInitToNew($machine);
        $this->addTransitionNewToWaiting($machine);
        $this->addTransitionAllToCanceled($machine);
        $this->addTransitionWaitingToReceived($machine);
    }

    /**
     * @param ModelEvent $event
     * @return Machine
     */
    public function createMachine(ModelEvent $event): Machine {
        $machine = new PaymentMachine($this->connection);
        $machine->setSymbolGenerator($this->symbolGeneratorFactory->createGenerator($event));
        $machine->setPriceCalculator($this->priceCalculatorFactory->createCalculator($event));
        return $machine;
    }

    /**
     * implicit transition when creating model (it's not executed only try condition!)
     * @param PaymentMachine $machine
     */
    private function addTransitionInitToNew(PaymentMachine &$machine) {
        $transition = $this->transitionFactory->createTransition(Machine::STATE_INIT, ModelPayment::STATE_NEW, _('Vytvoriť'));
        $transition->setCondition(
            function () {
                return $this->transitionFactory->getConditionDateBetween(new DateTime('2019-01-15'), new DateTime('2019-02-15'));
            });
        $machine->addTransition($transition);
    }

    /**
     * @param PaymentMachine $machine
     */
    private function addTransitionNewToWaiting(PaymentMachine &$machine) {
        $transition = $this->transitionFactory->createTransition(
            ModelPayment::STATE_NEW,
            ModelPayment::STATE_WAITING,
            _('Confirm payment')
        );

        $transition->setType(Transition::TYPE_SUCCESS);
        $transition->setCondition(function (ModelPayment $eventPayment) {
            return $this->transitionFactory->getConditionEventRole($eventPayment->getEvent(), $eventPayment, 'org') ||
                $this->transitionFactory->getConditionOwnerAssertion($eventPayment->getPerson());
        });
        $transition->beforeExecuteClosures[] = function (ModelPayment &$modelPayment) use ($machine) {
            $modelPayment->update($machine->getSymbolGenerator()->create($modelPayment));
            $modelPayment->updatePrice($machine->getPriceCalculator());
        };
        $transition->afterExecuteClosures[] = $this->transitionFactory->createMailCallback('fyziklani13/payment/create',
            $this->getMailSetupCallback(_('Confirm payment #%s')));

        $machine->addTransition($transition);
    }

    /**
     * @param string $subject
     * @return \Closure
     */
    private function getMailSetupCallback(string $subject): \Closure {
        return function (IStateModel $model) use ($subject): Message {
            $message = new Message();
            if ($model instanceof ModelPayment) {
                $message->setSubject(\sprintf($subject, $model->getPaymentId()));
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
            $transition = $this->transitionFactory->createTransition($state, ModelPayment::STATE_CANCELED, _('Zrusit platbu'));
            $transition->setType(Transition::TYPE_DANGER);
            $transition->setCondition(function () {
                return true;
            });
            $transition->beforeExecuteClosures[] = $this->getClosureDeleteRows();
            $machine->addTransition($transition);
        }
    }

    /**
     * @param PaymentMachine $machine
     */
    private function addTransitionWaitingToReceived(PaymentMachine &$machine) {
        $transition = $this->transitionFactory->createTransition(ModelPayment::STATE_WAITING, ModelPayment::STATE_RECEIVED, _('Zaplatil'));
        $transition->beforeExecuteClosures[] = function (ModelPayment $modelPayment) {
            foreach ($modelPayment->getRelatedPersonAccommodation() as $personAccommodation) {
                $personAccommodation->updateState(ModelEventPersonAccommodation::STATUS_PAID);
            }
        };
        $transition->afterExecuteClosures[] = $this->transitionFactory->createMailCallback('fyziklani13/payment/receive',
            $this->getMailSetupCallback(_('We are receive payment #%s')));

        $transition->setCondition(function (ModelPayment $eventPayment) {
            return $this->transitionFactory->getConditionDateBetween(new DateTime('2019-01-01'), new DateTime('2019-02-15'))
                && $this->transitionFactory->getConditionEventRole($eventPayment->getEvent(), $eventPayment, 'org');
        });
        $transition->setType(Transition::TYPE_SUCCESS);
        $machine->addTransition($transition);
    }

    /**
     * @return \Closure
     */
    private function getClosureDeleteRows(): \Closure {
        return function (ModelPayment $modelPayment) {
            foreach ($modelPayment->related(\DbNames::TAB_PAYMENT_ACCOMMODATION, 'payment_id') as $row) {
                $row->delete();
            }
        };
    }

    /**
     * @param string $type
     * @param $args
     * @return bool
     */
    private function getCondition(string $type, $args): bool {
        switch ($type) {
            case 'dateTo':
                return $this->transitionFactory->getConditionDateTo($args['dateTo']);
            case 'dataFrom':
                return $this->transitionFactory->getConditionDateFrom($args['dateFrom']);
            case 'dateBetween':
                return $this->transitionFactory->getConditionDateBetween($args['dateFrom'], $args['dateTo']);
            default:
                throw new NotImplementedException();

        }
    }
}
