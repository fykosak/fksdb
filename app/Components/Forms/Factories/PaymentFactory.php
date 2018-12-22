<?php


namespace FKSDB\Components\Forms\Factories;


use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Forms\Controls\Payment\DetailControl;
use FKSDB\Components\Forms\Controls\Payment\PaymentSelectField;
use FKSDB\Components\Forms\Factories\Payment\CurrencyField;
use FKSDB\Payment\Transition\PaymentMachine;
use FKSDB\ORM\ModelEvent;
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
        $currencyField = new CurrencyField();
        $currencyField->setRequired(true);
        $form->addComponent($currencyField, 'currency');
        $form->addComponent(new PaymentSelectField($this->serviceEventPersonAccommodation, $event), 'payment_accommodation');
        $form->addSubmit('save', _('Save'));
        return $control;
    }

    public function createCreateForm(PaymentMachine $machine, ModelEvent $event) {
        $control = new FormControl();
        $form = $control->getForm();
        $currencyField = new CurrencyField();
        $currencyField->setRequired(true);
        $form->addComponent($currencyField, 'currency');
        $form->addComponent(new PaymentSelectField($this->serviceEventPersonAccommodation, $event), 'payment_accommodation');
        $this->appendTransitionsButtons(null, $machine, $form);
        return $control;
    }

    public function createDetailControl(ModelPayment $modelPayment, ITranslator $translator, PaymentMachine $machine) {

        $control = new DetailControl($translator, $machine->getPriceCalculator(), $modelPayment);
        $form = $control->getFormControl()->getForm();
        if ($modelPayment->canEdit()) {
            $form->addSubmit('edit', _('Edit payment'));
        }

        $this->appendTransitionsButtons($modelPayment, $machine, $form);
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
