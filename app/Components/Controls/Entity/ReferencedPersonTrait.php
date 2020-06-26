<?php

namespace FKSDB\Components\Controls\Entity;

use FKSDB\Components\Forms\Controls\Autocomplete\AutocompleteSelectBox;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\ORM\Services\ServicePerson;

trait ReferencedPersonTrait {
    /**
     * @var PersonFactory
     */
    private $personFactory;

    /**
     * @var ServicePerson
     */
    private $servicePerson;

    protected function createPersonSelect(): AutocompleteSelectBox {
        return $this->personFactory->createPersonSelect(true, _('Person'), new PersonProvider($this->servicePerson));
    }

    /**
     * @param PersonFactory $personFactory
     * @param ServicePerson $servicePerson
     * @return void
     */
    public function injectPersonTrait(PersonFactory $personFactory, ServicePerson $servicePerson) {
        $this->personFactory = $personFactory;
        $this->servicePerson = $servicePerson;
    }
}
