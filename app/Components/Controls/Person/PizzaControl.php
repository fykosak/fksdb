<?php

namespace FKSDB\Components\Controls\Person;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Components\Controls\DBReflection\ValuePrinter\ValuePrinterComponent;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\Services\ServicePerson;
use Nette\Application\UI\Form;

class PizzaControl extends BaseComponent {

    private array $persons = [];

    private ServicePerson $servicePerson;

    private PersonFactory $personFactory;

    final public function injectPrimary(ServicePerson $servicePerson, PersonFactory $personFactory): void {
        $this->servicePerson = $servicePerson;
        $this->personFactory = $personFactory;
    }

    /**
     * @return FormControl
     * @throws BadTypeException
     */
    protected function createComponentForm(): FormControl {
        $control = new FormControl($this->getContext());
        $form = $control->getForm();
        $personsField = $this->personFactory->createPersonSelect(true, _('Persons'), new PersonProvider($this->servicePerson));
        $personsField->setMultiSelect(true);
        $form->addComponent($personsField, 'persons');
        $form->addSubmit('submit', _('Get pizza!'));
        $form->onSuccess[] = function (Form $form) {
            $values = $form->getValues();
            foreach ($values['persons'] as $personId) {
                $this->persons[] = $this->servicePerson->findByPrimary($personId);
            }
        };
        return $control;
    }

    public function render(): void {
        $this->template->persons = $this->persons;
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'layout.latte');
        $this->template->render();
    }

    protected function createComponentValuePrinter(): ValuePrinterComponent {
        return new ValuePrinterComponent($this->getContext());
    }
}
