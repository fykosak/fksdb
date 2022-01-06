<?php

declare(strict_types=1);

declare(strict_types=1);

namespace FKSDB\Components\EntityForms;

use FKSDB\Components\Forms\Controls\Autocomplete\AutocompleteSelectBox;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\Models\ORM\Services\ServicePerson;

trait ReferencedPersonTrait
{

    protected PersonFactory $personFactory;
    protected ServicePerson $servicePerson;

    protected function createPersonSelect(): AutocompleteSelectBox
    {
        return $this->personFactory->createPersonSelect(true, _('Person'), new PersonProvider($this->servicePerson));
    }

    final public function injectPersonTrait(PersonFactory $personFactory, ServicePerson $servicePerson): void
    {
        $this->personFactory = $personFactory;
        $this->servicePerson = $servicePerson;
    }
}
