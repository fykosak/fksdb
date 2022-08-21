<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms;

use FKSDB\Components\Forms\Controls\Autocomplete\AutocompleteSelectBox;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\Models\ORM\Services\PersonService;

trait ReferencedPersonTrait
{

    protected PersonFactory $personFactory;
    protected PersonService $personService;

    protected function createPersonSelect(): AutocompleteSelectBox
    {
        return $this->personFactory->createPersonSelect(true, _('Person'), new PersonProvider($this->personService));
    }

    final public function injectPersonTrait(PersonFactory $personFactory, PersonService $personService): void
    {
        $this->personFactory = $personFactory;
        $this->personService = $personService;
    }
}
