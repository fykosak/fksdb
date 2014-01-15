<?php

namespace FKS\Components\Forms\Controls;

use FKS\Utils\Promise;
use FKSDB\Components\Forms\Containers\PersonContainer;
use FKSDB\Components\Forms\Factories\PersonFactory;
use ModelPerson;
use ModelPostContact;
use Nette\Forms\Container;
use Nette\Forms\Controls\HiddenField;
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

    /**
     * @var Promise
     */
    private $promise;

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

    public function getPromise() {
        return $this->promise;
    }

    public function setPromise(Promise $promise) {
        $this->promise = $promise;
    }

    public function setValue($person) {
        $isPromise = ($person instanceof Promise || $person === self::VALUE_PROMISE);
        if (!($person instanceof ModelPerson) && !$isPromise) {
            $person = $this->servicePerson->findByPrimary($person);
        }
        $container = $this->personContainer;
        if ($isPromise) {
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
        if ($isPromise) {
            $value = self::VALUE_PROMISE;
            if ($person instanceof Promise) {
                $this->promise = $person;
            }
        } else if ($person instanceof ModelPerson) {
            $value = $person->getPrimary();
        } else {
            $value = $person;
        }
        parent::setValue($value);
    }

    public function getValue() {
        if ($this->promise) {
            return $this->promise->getValue();
        }

        return parent::getValue();
    }

    private function setFilledFields(ModelPerson $person) {
        foreach ($this->personContainer->getComponents() as $sub => $subcontainer) {
            if (!$subcontainer instanceof Container) {
                continue;
            }

            foreach ($subcontainer->getComponents() as $fieldName => $control) {
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
            case 'person':
                return $person[$field];
            case 'person_info':
                return ($info = $person->getInfo()) ? $info[$field] : null;
            case 'person_history':
                return ($history = $person->getHistory($this->acYear)) ? $history[$field] : null;
            case 'post_contact':
                if ($field == 'type') {
                    return ModelPostContact::TYPE_PERMANENT; //TODO distinquish delivery and permanent address
                } else if ($field == 'address') {
                    return $person->getPermanentAddress(); //TODO distinquish delivery and permanent address
                }
        }
    }

}
