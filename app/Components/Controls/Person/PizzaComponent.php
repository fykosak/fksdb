<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Person;

use Fykosak\Utils\BaseComponent\BaseComponent;
use FKSDB\Components\Controls\ColumnPrinter\ColumnPrinterComponent;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Services\PersonService;
use Nette\Application\UI\Form;

class PizzaComponent extends BaseComponent
{

    private array $persons = [];
    private PersonService $personService;
    private PersonFactory $personFactory;

    final public function injectPrimary(PersonService $personService, PersonFactory $personFactory): void
    {
        $this->personService = $personService;
        $this->personFactory = $personFactory;
    }

    /**
     * @throws BadTypeException
     */
    protected function createComponentForm(): FormControl
    {
        $control = new FormControl($this->getContext());
        $form = $control->getForm();
        $personsField = $this->personFactory->createPersonSelect(
            true,
            _('Persons'),
            new PersonProvider($this->personService)
        );
        $personsField->setMultiSelect(true);
        $form->addComponent($personsField, 'persons');
        $form->addSubmit('submit', _('Get pizza!'));
        $form->onSuccess[] = function (Form $form) {
            $values = $form->getValues();
            foreach ($values['persons'] as $personId) {
                $this->persons[] = $this->personService->findByPrimary($personId);
            }
        };
        return $control;
    }

    final public function render(): void
    {
        $this->getTemplate()->persons = $this->persons;
        $this->getTemplate()->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.latte');
    }

    protected function createComponentValuePrinter(): ColumnPrinterComponent
    {
        return new ColumnPrinterComponent($this->getContext());
    }
}
