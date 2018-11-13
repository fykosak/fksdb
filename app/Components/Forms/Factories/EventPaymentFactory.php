<?php


namespace FKSDB\Components\Forms\Factories;


use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Controls\EventPayment\DetailControl;
use FKSDB\EventPayment\PriceCalculator\PriceCalculator;
use FKSDB\EventPayment\Transition\Machine;
use FKSDB\ORM\ModelEventPayment;
use Nette\Application\UI\Form;
use Nette\Localization\ITranslator;

class EventPaymentFactory {
    public function createEditForm($isOrg = false) {
        $control = new FormControl();
        $form = $control->getForm();
        if ($isOrg) {

        }
        $form->addText('data', _('Data')); // todo react?
        $form->addSubmit('save', _('Save'));
        return $control;
    }

    public function createCreateForm(Machine $machine) {
        $control = new FormControl();
        $form = $control->getForm();
        $form->addText('data', _('Data'));
        $this->appendTransitionsButtons($machine, $form);
        return $control;
    }

    public function createConfirmControl(ModelEventPayment $modelEventPayment, PriceCalculator $calculator, ITranslator $translator, Machine $machine) {

        $control = new DetailControl($translator, $calculator, $modelEventPayment);
        $form = $control->getFormControl()->getForm();
        $form->addSubmit('edit', _('Edit payment'));
        $this->appendTransitionsButtons($machine, $form);
        return $control;
    }

    public function createDetailControl(ModelEventPayment $modelEventPayment, PriceCalculator $calculator, ITranslator $translator, Machine $machine) {

        $control = new DetailControl($translator, $calculator, $modelEventPayment);
        return $control;
    }

    private function appendTransitionsButtons(Machine $machine, Form $form) {
        $transitions = $machine->getAvailableTransitions();
        foreach ($transitions as $transition) {
            $button = $form->addSubmit($transition->getId(), $transition->getLabel());
            $button->getControlPrototype()->class .= 'btn btn-' . $transition->getType();
        }

    }
}
