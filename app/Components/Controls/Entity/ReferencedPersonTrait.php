<?php

namespace FKSDB\Components\Controls\Entity;

use FKSDB\Components\Forms\Controls\Autocomplete\AutocompleteSelectBox;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\ORM\Services\ServicePerson;

/**
 * Trait ReferencedPersonTrait
 * @author Michal Červeňák <miso@fykos.cz>
 */
trait ReferencedPersonTrait {

    protected PersonFactory $personFactory;
    protected ServicePerson $servicePerson;

    protected function createPersonSelect(): AutocompleteSelectBox {
        return $this->personFactory->createPersonSelect(true, _('Person'), new PersonProvider($this->servicePerson));
    }

    final public function injectPersonTrait(PersonFactory $personFactory, ServicePerson $servicePerson): void {
        $this->personFactory = $personFactory;
        $this->servicePerson = $servicePerson;
    }
}
