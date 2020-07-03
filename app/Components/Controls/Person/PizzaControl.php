<?php

namespace FKSDB\Components\Controls\Person;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\DatabaseReflection\ValuePrinterComponent;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Services\ServicePerson;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;

class PizzaControl extends BaseComponent {

    /** @var ModelPerson[] */
    private $persons = [];
    /** @var ServicePerson */
    private $servicePerson;
    /** @var PersonFactory */
    private $personFactory;

    /**
     * @param ServicePerson $servicePerson
     * @param PersonFactory $personFactory
     * @return void
     */
    public function injectPrimary(ServicePerson $servicePerson, PersonFactory $personFactory) {
        $this->servicePerson = $servicePerson;
        $this->personFactory = $personFactory;
    }

    /**
     * @return FormControl
     * @throws BadRequestException
     */
    protected function createComponentForm(): FormControl {
        $control = new FormControl();
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

    public function render() {
        $this->template->persons = $this->persons;
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'layout.latte');
        $this->template->render();
    }

    protected function createComponentValuePrinter(): ValuePrinterComponent {
        return new ValuePrinterComponent($this->getContext());
    }
}
