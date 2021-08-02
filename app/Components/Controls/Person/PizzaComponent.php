<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Person;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Components\Controls\ColumnPrinter\ColumnPrinterComponent;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Services\ServicePerson;
use Nette\Application\UI\Form;

class PizzaComponent extends BaseComponent
{

    private array $persons = [];

    private ServicePerson $servicePerson;

    private PersonFactory $personFactory;

    final public function injectPrimary(ServicePerson $servicePerson, PersonFactory $personFactory): void
    {
        $this->servicePerson = $servicePerson;
        $this->personFactory = $personFactory;
    }

    /**
     * @return FormControl
     * @throws BadTypeException
     */
    protected function createComponentForm(): FormControl
    {
        $control = new FormControl($this->getContext());
        $form = $control->getForm();
        $personsField = $this->personFactory->createPersonSelect(
            true,
            _('Persons'),
            new PersonProvider($this->servicePerson)
        );
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

    final public function render(): void
    {
        $this->template->persons = $this->persons;
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.latte');
    }

    protected function createComponentValuePrinter(): ColumnPrinterComponent
    {
        return new ColumnPrinterComponent($this->getContext());
    }
}
