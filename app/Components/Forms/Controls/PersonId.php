<?php

namespace FKS\Components\Forms\Controls;

use FKSDB\Components\Forms\Containers\PersonContainer;
use FKSDB\Components\Forms\Factories\PersonFactory;
use ModelPerson;
use Nette\Forms\Container;
use Nette\Forms\Controls\HiddenField;
use Nette\InvalidStateException;
use ServicePerson;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class PersonId extends HiddenField {

    const VALUE_PROMISE = '__promise';

    /**
     * @var PersonContainer
     */
    private $personContainer;

    /**
     * @var ServicePerson
     */
    private $servicePerson;

    /**
     * @var int
     */
    private $acYear;

    /**
     * @var enum
     */
    private $filledFields;

    

    //TODO fields may be not necessary, the data are also in container iteself
    function __construct(ServicePerson $servicePerson, $acYear, $filledFields) {
        parent::__construct();
        $this->servicePerson = $servicePerson;
        $this->acYear = $acYear;
        $this->filledFields = $filledFields;
    }

    public function getPersonContainer() {
        return $this->personContainer;
    }

    public function setPersonContainer(PersonContainer $personContainer) {
        $this->personContainer = $personContainer;
    }

    public function setValue($person) {
        if (!$person instanceof ModelPerson && $person !== self::VALUE_PROMISE) {
            $person = $this->servicePerson->findByPrimary($person);
        }
        $container = $this->personContainer;
        if ($person === self::VALUE_PROMISE) {
            $container->showSearch(false);
            $container->setClearButton(true);
        } else if (!$person) {
            $container->showSearch(true);
            $container->setClearButton(false);
        } else {
            $container->showSearch(false);
            $container->setClearButton(true);

            $this->setFilledFields($person);
        }

        parent::setValue(($person instanceof ModelPerson) ? $person->getPrimary() : $person);
    }

    private function setFilledFields(ModelPerson $person) {
        foreach ($this->personContainer->getComponents() as $sub => $controls) {
            if (!$controls instanceof Container) {
                continue;
            }

            foreach ($controls->getComponents() as $fieldName => $control) {
                $value = $this->getPersonValue($person, $sub, $fieldName);
                if ($value) {
                    if ($this->filledFields == PersonFactory::EX_HIDDEN) {
                        $this->personContainer[$sub]->removeComponent($control);
                    } else if ($this->filledFields == PersonFactory::EX_DISABLED) {
                        $control->setDisabled();
                        $control->setValue($value);
                    } else if ($this->filledFields == PersonFactory::EX_MODIFIABLE) {
                        $control->setValue($value);
                    }
                }
            }
        }
    }

    public function setDisabled($value = TRUE) {
        //TODO
        parent::setDisabled($value);
    }

    private function getPersonValue(ModelPerson $person = null, $sub, $field) {
        if (!$person) {
            return null;
        }
        switch ($sub) {
            case 'base':
                return $person[$field];
            case 'info':
                return ($info = $person->getInfo()) ? $info[$field] : null;
            case 'history':
                return ($history = $person->getHistory($this->acYear)) ? $history[$field] : null;
            case 'address':
                return $person->getDeliveryAddress(); //TODO distinquish delivery and permanent address
        }
    }

}
