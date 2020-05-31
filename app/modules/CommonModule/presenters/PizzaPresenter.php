<?php

namespace CommonModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Services\ServicePerson;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;

/**
 * Class PizzaPresenter
 * *
 */
class PizzaPresenter extends BasePresenter {
    /**
     * @var ModelPerson[]
     */
    private $persons = [];
    /**
     * @var PersonFactory
     */
    private $personFactory;
    /**
     * @var ServicePerson
     */
    private $servicePerson;

    /**
     * @param PersonFactory $personFactory
     * @return void
     */
    public function injectPersonFactory(PersonFactory $personFactory) {
        $this->personFactory = $personFactory;
    }

    /**
     * @param ServicePerson $servicePerson
     * @return void
     */
    public function injectServicePerson(ServicePerson $servicePerson) {
        $this->servicePerson = $servicePerson;
    }

    /**
     * @return FormControl
     * @throws BadRequestException
     */
    public function createComponentPersonsForm(): FormControl {
        $control = new FormControl($this->getContext());
        $form = $control->getForm();
        $personsField = $this->personFactory->createPersonSelect(true, _('Persons'), new PersonProvider($this->servicePerson));
        $personsField->setMultiSelect(true);
        $form->addComponent($personsField, 'persons');
        $form->addSubmit('submit', _('Get pizza information!'));
        $form->onSuccess[] = function (Form $form) {
            $values = $form->getValues();
            foreach ($values->persons as $personId) {
                $this->persons[] = $this->servicePerson->findByPrimary($personId);
            }
        };
        return $control;
    }

    public function renderDefault() {
        $this->template->persons = $this->persons;
    }

    public function titleDefault() {
        $this->setTitle(_('Pizza'),'fa fa-cutlery');
    }
}
