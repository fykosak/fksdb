<?php


namespace PublicModule;


use Events\Payment\MachineFactory;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Factories\ReferencedPerson\ReferencedPersonFactory;
use FKSDB\ORM\ModelEvent;
use FKSDB\ORM\ModelEventPayment;
use Nette\Application\UI\Form;
use Nette\Diagnostics\Debugger;

class EventPaymentPresenter extends BasePresenter {
    /**
     * @var \ServiceEventPayment
     */
    private $serviceEventPayment;

    /**
     * @var ReferencedPersonFactory
     */
    private $referencedPersonFactory;

    /**
     * @var integer
     * @persistent
     */
    public $eventId;
    /**
     * @var MachineFactory
     */
    private $transitionFactory;
    /**
     * @var \ServiceEvent
     */
    private $serviceEvent;

    public function injectServiceEventPayment(\ServiceEventPayment $serviceEventPayment) {
        $this->serviceEventPayment = $serviceEventPayment;
    }

    public function injectServicePersonFactory(ReferencedPersonFactory $referencedPersonFactory) {
        $this->referencedPersonFactory = $referencedPersonFactory;
    }

    public function injectEventTransitionFactory(MachineFactory $transitionFactory) {
        $this->transitionFactory = $transitionFactory;
    }

    public function injectServiceEvent(\ServiceEvent $serviceEvent) {
        $this->serviceEvent = $serviceEvent;
    }

    protected function createComponentCreateForm() {
        $control = new FormControl();
        $form = $control->getForm();
        $machine = $this->getMachine();
        $transitions = $machine->getAvailableTransitions();
        foreach ($transitions as $transition) {
            $form->addSubmit($transition->getId(), $transition->getLabel());

        }

        $form->onSuccess[] = function (Form $form) {

            /**
             * @var $model ModelEventPayment
             */
            $model = $this->serviceEventPayment->createNew([
                'person_id' => 324,
                'event_id' => $this->eventId,
                'data' => '',
                'state' => null,
                'price_kc' => 251,
                'price_eur' => 101,
                'constant_symbol' => 1234,
                'variable_symbol' => 1234,
                'specific_symbol' => 1234,
            ]);
            $this->serviceEventPayment->save($model);
            
            foreach ($form->getComponents() as $name => $component) {
                if ($form->isSubmitted() === $component) {
                    Debugger::barDump($name);
                    $model->executeTransition($this->getMachine(), $name);
                }
            }
            //
            Debugger::barDump($model);

        };
        return $control;
    }

    private function getEvent() {
        return ModelEvent::createFromTableRow($this->serviceEvent->findByPrimary($this->eventId));
    }

    private function getMachine() {
        return $this->transitionFactory->setUpMachine($this->getEvent());
    }
}
