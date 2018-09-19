<?php

namespace FKSDB\Components\Forms\Containers;

use FKSDB\Components\Forms\Controls\PersonId;
use FKSDB\Utils\Promise;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Forms\Factories\PersonFactory;
use Nette\Forms\Container;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;
use Nette\InvalidStateException;
use Nette\Utils\Arrays;
use Persons\PersonHandler2;
use Persons\ResolutionException;
use ServicePerson;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class PersonContainer extends Container {

    const SEARCH_EMAIL = 'email';
    const SEARCH_NAME = 'name';
    const SEARCH_NONE = 'none';
    const CONTROL_SEARCH = '_c_search';
    const SUBMIT_SEARCH = '__search';
    const SUBMIT_CLEAR = '__clear';

    private $searchType;
    private $hiddenComponents = array();

    /**
     * @var PersonId
     */
    private $personId;

    /**
     *
     * @var PersonFactory
     */
    private $personFactory;

    /**
     * @var PersonProvider
     */
    private $personProvider;

    /**
     * @var ServicePerson
     */
    private $servicePerson;

    /**
     * @var PersonHandler2
     */
    private $handler;
    private $acYear;

    /**
     * @var boolean
     */
    private $allowClear = true;
    private $createResolution = PersonHandler2::RESOLUTION_EXCEPTION;
    private $updateResolution = PersonHandler2::RESOLUTION_OVERWRITE;

    public function __construct(PersonId $personId, PersonFactory $personFactory, PersonProvider $personProvider, ServicePerson $servicePerson, PersonHandler2 $handler, $acYear) {
        parent::__construct();
        $this->monitor('Nette\Forms\Form');

        $this->personId = $personId;
        $this->personFactory = $personFactory;
        $this->personProvider = $personProvider;
        $this->servicePerson = $servicePerson;
        $this->handler = $handler;
        $this->acYear = $acYear;

        $this->createClearButton();
        $this->createSearchButton();
        $personId->setPersonContainer($this);
    }

    public function getSearchType() {
        return $this->searchType;
    }

    /**
     * @param enum $searchType
     */
    public function setSearchType($searchType) {
        $this->searchType = $searchType;
        switch ($this->searchType) {
            case PersonContainer::SEARCH_EMAIL:
                $this->addText(self::CONTROL_SEARCH, _('E-mail'))
                        ->addCondition(Form::FILLED)
                        ->addRule(Form::EMAIL, _('Neplatný tvar e-mailu.'));
                break;
            case PersonContainer::SEARCH_NAME:
                $control = $this->personFactory->createPersonSelect(true, _('Jméno'), $this->personProvider);
                $this->addComponent($control, self::CONTROL_SEARCH);
                break;
            case PersonContainer::SEARCH_NONE:
                $this->personId->setValue(null); // TODO must be filled with Javascript when family_name/other_name is filled
                break;
        }
    }

    public function getAllowClear() {
        return $this->allowClear;
    }

    public function setAllowClear($allowClear) {
        $this->allowClear = $allowClear;
    }

    public function getCreateResolution() {
        return $this->createResolution;
    }

    public function setCreateResolution($createResolution) {
        $this->createResolution = $createResolution;
    }

    public function getUpdateResolution() {
        return $this->updateResolution;
    }

    public function setUpdateResolution($updateResolution) {
        $this->updateResolution = $updateResolution;
    }

    public function showSearch($value) {
        static $searchComponents = array(
    self::CONTROL_SEARCH,
    self::SUBMIT_SEARCH,
        );
        if ($this->getSearchType() == PersonContainer::SEARCH_NONE) {
            $value = false;
        }

        foreach ($this->hiddenComponents as $name => $component) {
            if ($value == !!Arrays::grep($searchComponents, "/^$name/")) {
                $this->addComponent($component, $name);
                unset($this->hiddenComponents[$name]);
            }
        }


        foreach ($this->getComponents() as $name => $component) {
            if ($value == !Arrays::grep($searchComponents, "/^$name/")) {
                $this->removeComponent($component);
                $this->hiddenComponents[$name] = $component;
            }
        }
    }

    public function setClearButton($value) {
        if (!$this->getAllowClear()) {
            $value = false;
        }
        if ($value) {
            $component = Arrays::get($this->hiddenComponents, self::SUBMIT_CLEAR, null);
            if ($component) {
                $this->addComponent($component, self::SUBMIT_CLEAR);
                unset($this->hiddenComponents[self::SUBMIT_CLEAR]);
            }
        } else {
            $component = $this->getComponent(self::SUBMIT_CLEAR, false);
            if ($component) {
                $this->hiddenComponents[self::SUBMIT_CLEAR] = $component;
                $this->removeComponent($component);
            }
        }
    }

    //TODO move to person ID?
    private function createOrUpdatePerson(Form $form) {
        $personId = $this->personId->getValue();
        if (!$personId) {
            return;
        }
        $values = $this->getValues();
        $promise = new Promise(function() use($form,$personId, $values) {
                    if ($personId === PersonId::VALUE_PROMISE) {
                        try {
                            $person = $this->handler->createFromValues($values, $this->acYear, $this->getCreateResolution());
                            return $person;
                        } catch (ResolutionException $e) {
                            $form->addError(_('Data se neshodují s evidovanou osobou. Byla doplněna evidovaná data.')); //TODO should contain GUI name of the container
                            $this->personId->setValue($e->getPerson());
                            throw $e;
                        }
                    } else if ($personId) {
                        $person = $this->servicePerson->findByPrimary($personId);
                        $this->handler->update($person, $values, $this->acYear, $this->getUpdateResolution());
                        return $personId;
                    }
                });
        $this->personId->setValue($personId);
        $this->personId->setPromise($promise);
    }

    private $attachedOnValidate = false;

    protected function attached($obj) {
        parent::attached($obj);
        if (!$this->attachedOnValidate && $obj instanceof Form) {
            $obj->onValidate[] = function(Form $form) {
                        $this->createOrUpdatePerson($form);
                    };
            $this->attachedOnValidate = true;
        }
    }

    private function createClearButton() {
        $this->addSubmit(self::SUBMIT_CLEAR, 'X')
                        ->setValidationScope(false)
                ->onClick[] = function(SubmitButton $submit) {
                    $this->personId->setValue(null);
                };
    }

    private function createSearchButton() {
        $this->addSubmit(self::SUBMIT_SEARCH, 'Najít')
                        ->setValidationScope(false)
                ->onClick[] = function(SubmitButton $submit) {
                    $term = $this->getComponent(self::CONTROL_SEARCH)->getValue();
                    $person = $this->findPerson($term);
                    $values = array();
                    if (!$person) {
                        $person = PersonId::VALUE_PROMISE;
                        $values = $this->getPersonSearchData($term);
                    }
                    $this->personId->setValue($person);
                    $this->setValues($values);
                };
    }

    private function findPerson($term) {
        switch ($this->searchType) {
            case PersonContainer::SEARCH_EMAIL:
                return $this->servicePerson->findByEmail($term);
            case PersonContainer::SEARCH_NAME:
                return $this->servicePerson->findByPrimary($term);
        }
        return null;
    }

    private function getPersonSearchData($term) {
        switch ($this->searchType) {
            case PersonContainer::SEARCH_EMAIL:
                return array(
                    'person_info' => array('email' => $term)
                );
        }

        return array();
    }

}

