<?php


namespace FKSDB\Components\Forms\Factories;


use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\ORM\ModelPerson;

class EventPaymentFactory {
    public function createEditForm(ModelPerson $modelPerson) {
        $control = new FormControl();
        $form = $control->getForm();
        $form->addHidden('person_id');
        $form->addText('person', _('Person'))
            ->setDisabled(true)
            ->setValue($modelPerson->getFullName());
        $form->addText('data', _('Data'));

        return $control;
    }

    public function createPreview(ModelPerson $modelPerson) {
        $control = new FormControl();
        $form = $control->getForm();
        $form->addHidden('person_id');
        $form->addText('person', _('Person'))
            ->setDisabled(true)
            ->setValue($modelPerson->getFullName());
        $form->addText('data', _('Data'))->setDisabled(true);
        $form->addText('price_kc', _('Price Kč'))->setDisabled(true);
        $form->addText('price_eur', _('Price €'))->setDisabled(true);
        $form->addText('constant_symbol', _('Constant symbol'))->setDisabled(true);
        $form->addText('variable_symbol', _('Variable symbol'))->setDisabled(true);
        $form->addText('specific_symbol', _('Specific symbol'))->setDisabled(true);
        return $control;
    }

    public function createCreateForm() {
        $control = new FormControl();
        $form = $control->getForm();
        $form->addText('data', _('Data'));
        return $control;
    }

    public function createConfirmForm(ModelPerson $modelPerson) {
        $control = new FormControl();
        $form = $control->getForm();
        $form->addHidden('person_id');
        $form->addText('person', _('Person'))
            ->setDisabled(true)
            ->setValue($modelPerson->getFullName());
        $form->addText('data', _('Data'))->setDisabled(true);
        $form->addText('price_kc', _('Price Kč'))->setDisabled(true);
        $form->addText('price_eur', _('Price €'))->setDisabled(true);

        return $control;
    }
}
