<?php


namespace FKSDB\Components\Forms\Factories;


use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Forms\Controls\EventPayment\DetailControl;
use FKSDB\Components\Forms\Factories\EventPayment\CurrencyField;
use FKSDB\EventPayment\Transition\PaymentMachine;
use FKSDB\ORM\ModelEvent;
use FKSDB\ORM\ModelEventAccommodation;
use FKSDB\ORM\ModelEventPersonAccommodation;
use FKSDB\ORM\ModelPayment;
use Nette\Application\UI\Form;
use Nette\Localization\ITranslator;

class PaymentFactory {
    /**
     * @var PersonFactory
     */
    private $personFactory;
    /**
     * @var PersonProvider
     */
    private $personProvider;
    /**
     * @var \ServiceEventPersonAccommodation
     */
    private $serviceEventPersonAccommodation;

    public function __construct(PersonFactory $personFactory, PersonProvider $personProvider, \ServiceEventPersonAccommodation $serviceEventPersonAccommodation) {
        $this->personFactory = $personFactory;
        $this->personProvider = $personProvider;
        $this->serviceEventPersonAccommodation = $serviceEventPersonAccommodation;
    }

    public function createEditForm(bool $isOrg, ModelEvent $event) {
        $control = new FormControl();
        $form = $control->getForm();

        if ($isOrg) {
            $form->addComponent($this->personFactory->createPersonSelect(true, _('Person'), $this->personProvider), 'person_id');
        }
        $form->addComponent(new CurrencyField(), 'currency');
        $this->appendDataContainer($form, $event);
        $form->addSubmit('save', _('Save'));
        return $control;
    }

    public function createCreateForm(PaymentMachine $machine, ModelEvent $event) {
        $control = new FormControl();
        $form = $control->getForm();
        $currencyField = new CurrencyField();
        $currencyField->setRequired(true);
        $form->addComponent($currencyField, 'currency');
        $this->appendDataContainer($form, $event);
        $this->appendTransitionsButtons(null, $machine, $form);
        return $control;
    }

    private function appendDataContainer(Form &$form, ModelEvent $event) {
        $container = new ModelContainer();
        foreach ($event->getEventAccommodations() as $accRow) {
            $model = ModelEventAccommodation::createFromTableRow($accRow);
            foreach ($model->related(\DbNames::TAB_EVENT_PERSON_ACCOMMODATION, 'event_accommodation_id') as $accPersRow) {
                $modelAccPerson = ModelEventPersonAccommodation::createFromTableRow($accPersRow);
                $container->addCheckbox($modelAccPerson->event_person_accommodation_id, $modelAccPerson->getLabel());
            };
        }
        $form->addComponent($container, 'payment_accommodation');
    }

    public function createDetailControl(ModelPayment $modelEventPayment, ITranslator $translator, PaymentMachine $machine) {

        $control = new DetailControl($translator, $machine->getPriceCalculator(), $modelEventPayment);
        $form = $control->getFormControl()->getForm();
        if ($modelEventPayment->canEdit()) {
            $form->addSubmit('edit', _('Edit payment'));
        }

        $this->appendTransitionsButtons($modelEventPayment, $machine, $form);
        return $control;
    }

    private function appendTransitionsButtons($model, PaymentMachine $machine, Form $form) {
        $transitions = $machine->getAvailableTransitions($model);
        foreach ($transitions as $transition) {
            $button = $form->addSubmit($transition->getId(), $transition->getLabel());
            $button->getControlPrototype()->addAttributes(['class' => 'btn btn-' . $transition->getType()]);
        }

    }
}
