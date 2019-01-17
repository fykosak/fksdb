<?php

namespace FKSDB\Payment\Transition\Transitions;

use FKSDB\ORM\ModelEvent;
use FKSDB\ORM\ModelEventPersonAccommodation;
use FKSDB\ORM\ModelPayment;
use FKSDB\Payment\PriceCalculator\PriceCalculatorFactory;
use FKSDB\Payment\SymbolGenerator\SymbolGeneratorFactory;
use FKSDB\Payment\Transition\PaymentMachine;
use FKSDB\Transitions\AbstractTransitionsGenerator;
use FKSDB\Transitions\Conditions\DateBetween;
use FKSDB\Transitions\IStateModel;
use FKSDB\Transitions\Logic\LogicOr;
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
    /**
     * @var \ServicePayment
     */
    private $servicePayment;

    public function __construct(\ServicePayment $servicePayment, Connection $connection, TransitionsFactory $transitionFactory, SymbolGeneratorFactory $symbolGeneratorFactory, PriceCalculatorFactory $priceCalculatorFactory) {
        parent::__construct($transitionFactory);
        $this->connection = $connection;
        $this->servicePayment = $servicePayment;
        $this->symbolGeneratorFactory = $symbolGeneratorFactory;
        $this->priceCalculatorFactory = $priceCalculatorFactory;
    }

    /**
     * @param Machine $machine
     * @throws BadRequestException
     */
    public function createTransitions(Machine &$machine) {
        if (!$machine instanceof PaymentMachine) {
            throw new BadRequestException(\sprintf(_('Expected class %s, got %s'), 'PaymentMachine', \get_class($machine)));
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
        $machine = new PaymentMachine(
            $event,
            $this->priceCalculatorFactory->createCalculator($event),
            $this->symbolGeneratorFactory->createGenerator($event),
            $this->connection,
            $this->servicePayment
        );
        return $machine;
    }

    /**
     * implicit transition when creating model (it's not executed only try condition!)
     * @param PaymentMachine $machine
     */
    private function addTransitionInitToNew(PaymentMachine &$machine) {
        $transition = $this->transitionFactory->createTransition(Machine::STATE_INIT, ModelPayment::STATE_NEW, _('Create'));
        $transition->setCondition(
            new LogicOr(
                new DateBetween(new DateTime('2019-01-18'), new DateTime('2019-02-15')),
                function () use ($machine): bool {
                    return $this->transitionFactory->getConditionEventRole($machine->getEvent(), ModelPayment::RESOURCE_ID, 'org');
                })
        );
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
        $transition->setCondition(
            new LogicOr(
                function (ModelPayment $model): bool {
                    return $this->transitionFactory->getConditionEventRole($model->getEvent(), $model, 'org');
                },
                function (ModelPayment $model): bool {
                    return $this->transitionFactory->getConditionOwnerAssertion($model->getPerson());
                })
        );
        $transition->beforeExecuteClosures[] = function (ModelPayment &$modelPayment) use ($machine) {
            $modelPayment->update($machine->getSymbolGenerator()->create($modelPayment));
            $modelPayment->updatePrice($machine->getPriceCalculator());
        };
        $transition->afterExecuteClosures[] = $this->transitionFactory->createMailCallback('fyziklani/fyziklani2019/payment/create',
            $this->getMailSetupCallback(_('Payment #%s created')));

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
            $transition->beforeExecuteClosures[] = function (ModelPayment $modelPayment) {
                $modelPayment->update(['price' => null]);
            };
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
        $transition->afterExecuteClosures[] = $this->transitionFactory->createMailCallback('fyziklani/fyziklani2019/payment/receive',
            $this->getMailSetupCallback(_('We are receive payment #%s')));

        $transition->setCondition(function (ModelPayment $eventPayment) {
            return $this->transitionFactory->getConditionEventRole($eventPayment->getEvent(), $eventPayment, 'org');
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
