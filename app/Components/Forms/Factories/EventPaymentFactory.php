<?php


namespace FKSDB\Components\Forms\Factories;


use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Forms\Controls\EventPayment\DetailControl;
use FKSDB\Components\Forms\Factories\EventPayment\CurrencyField;
use FKSDB\EventPayment\Transition\Machine;
use FKSDB\EventPayment\Transition\PaymentMachine;
use FKSDB\ORM\ModelEventPayment;
use Nette\Application\UI\Form;
use Nette\Localization\ITranslator;

class EventPaymentFactory {
    /**
     * @var PersonFactory
     */
    private $personFactory;
    /**
     * @var PersonProvider
     */
    private $personProvider;

    public function __construct(PersonFactory $personFactory, PersonProvider $personProvider) {
        $this->personFactory = $personFactory;
        $this->personProvider = $personProvider;
    }

    public function createEditForm(bool $isOrg) {
        $control = new FormControl();
        $form = $control->getForm();

        if ($isOrg) {
            $form->addComponent($this->personFactory->createPersonSelect(true, _('Person'), $this->personProvider), 'person_id');
        }
        $form->addComponent(new CurrencyField(), 'currency');
        $form->addText('data', _('Data')); // todo react?
        $form->addSubmit('save', _('Save'));
        return $control;
    }

    public function createCreateForm(PaymentMachine $machine) {
        $control = new FormControl();
        $form = $control->getForm();
        $currencyField = new CurrencyField();
        $currencyField->setRequired(true);
        $form->addComponent($currencyField, 'currency');
        $form->addText('data', _('Data'));
        $this->appendTransitionsButtons(null, $machine, $form);
        return $control;
    }

    public function createDetailControl(ModelEventPayment $modelEventPayment, ITranslator $translator, PaymentMachine $machine) {

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
