<?php


namespace OrgModule;


use Events\Payment\MachineFactory;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\Components\Grids\EventPaymentGrid;
use FKSDB\ORM\ModelPerson;
use Nette\NotImplementedException;

class EventPaymentPresenter extends EntityPresenter {
    /**
     * @var \ServiceEventPayment
     */
    private $serviceEventPayment;

    /**
     * @var PersonFactory
     */
    private $personFactory;

    /**
     * @var integer
     * @persistent
     */
    public $eventId;
    private $transitionFactory;

    public function injectServiceEventPayment(\ServiceEventPayment $serviceEventPayment) {
        $this->serviceEventPayment = $serviceEventPayment;
    }

    public function injectServicePersonFactory(PersonFactory $personFactory) {
        $this->personFactory = $personFactory;
    }

    public function injectEventTransitionFactory(MachineFactory $transitionFactory) {
        $this->transitionFactory = $transitionFactory;
    }

    protected function createComponentCreateComponent($name) {
        throw new NotImplementedException('use public GUI');
    }

    protected function createComponentGrid($name) {
        return new EventPaymentGrid($this->serviceEventPayment, $this->transitionFactory, $this->eventId);
    }

    protected function createComponentEditComponent($name) {
        $control = new FormControl();
        $form = $control->getForm();
        $form->addHidden('person_id');
        $form->addText('person', 'Osoba')
            ->setDisabled(true)
            ->setValue(ModelPerson::createFromTableRow($this->getModel()->person)->getFullName());


        return $control;
    }

    protected function loadModel($id) {
        return $this->serviceEventPayment->findByPrimary($id);
    }
}
