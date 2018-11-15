<?php


namespace FKSDB\Components\Forms\Factories;


use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Forms\Controls\EventPayment\DetailControl;
use FKSDB\EventPayment\PriceCalculator\PriceCalculator;
use FKSDB\EventPayment\Transition\Machine;
use FKSDB\ORM\ModelEventPayment;
use Nette\Application\UI\Form;
use Nette\Diagnostics\Debugger;
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
        Debugger::barDump($isOrg);
        if ($isOrg) {
            $form->addComponent($this->personFactory->createPersonSelect(true, _('Person'), $this->personProvider), 'person_id');
        }
        $form->addText('data', _('Data')); // todo react?
        $form->addSubmit('save', _('Save'));
        return $control;
    }

    public function createCreateForm(Machine $machine, $isOrg) {
        $control = new FormControl();
        $form = $control->getForm();
        $form->addText('data', _('Data'));
        $this->appendTransitionsButtons(null, $machine, $form, $isOrg);
        return $control;
    }

    public function createDetailControl(ModelEventPayment $modelEventPayment, PriceCalculator $calculator, ITranslator $translator, Machine $machine, $isOrg) {

        $control = new DetailControl($translator, $calculator, $modelEventPayment);
        $form = $control->getFormControl()->getForm();
        if ($modelEventPayment->canEdit()) {
            $form->addSubmit('edit', _('Edit payment'));
        }

        $this->appendTransitionsButtons($modelEventPayment->state, $machine, $form, $isOrg);
        return $control;
    }

    private function appendTransitionsButtons($state, Machine $machine, Form $form, $isOrg) {
        $transitions = $machine->getAvailableTransitions($state, $isOrg);
        foreach ($transitions as $transition) {
            $button = $form->addSubmit($transition->getId(), $transition->getLabel());
            $button->getControlPrototype()->addAttributes(['class' => 'btn btn-' . $transition->getType()]);
        }

    }
}
