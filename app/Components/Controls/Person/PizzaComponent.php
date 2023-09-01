<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Person;

use FKSDB\Components\Controls\ColumnPrinter\ColumnRendererComponent;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\PersonService;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Nette\Forms\Form;

class PizzaComponent extends BaseComponent
{
    /** @phpstan-var PersonModel[] */
    private array $persons = [];
    private PersonService $personService;
    private PersonFactory $personFactory;

    final public function injectPrimary(PersonService $personService, PersonFactory $personFactory): void
    {
        $this->personService = $personService;
        $this->personFactory = $personFactory;
    }

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
            /** @phpstan-var array{persons:int[]} $values */
            $values = $form->getValues('array');
            foreach ($values['persons'] as $personId) {
                $person = $this->personService->findByPrimary($personId);
                if ($person) {
                    $this->persons[] = $person;
                }
            }
        };
        return $control;
    }

    final public function render(): void
    {
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.latte', ['persons' => $this->persons]);
    }

    protected function createComponentValuePrinter(): ColumnRendererComponent
    {
        return new ColumnRendererComponent($this->getContext());
    }
}
