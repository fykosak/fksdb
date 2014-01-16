<?php

namespace FKSDB\Components\Forms\Factories\Events;

use Events\Machine\BaseMachine;
use Events\Model\Field;
use FKSDB\Components\Forms\Containers\PersonContainer;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Forms\Factories\PersonFactory as CorePersonFactory;
use FKSDB\Components\Forms\Factories\ReferencedPersonFactory;
use Nette\ComponentModel\Component;
use Nette\Forms\Container;
use Persons\PersonHandler2;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class PersonFactory extends AbstractFactory {

    private $fieldsDefinition;
    private $searchType;
    private $allowClear;
    private $fillingMode;
    private $resolution;

    /**
     * @var ReferencedPersonFactory
     */
    private $referencedPersonFactory;

    function __construct($fieldsDefinition, $searchType, $allowClear, $fillingMode, $resolution, ReferencedPersonFactory $referencedPersonFactory) {
        $this->fieldsDefinition = $fieldsDefinition;
        $this->searchType = $searchType;
        $this->allowClear = $allowClear;
        $this->fillingMode = $fillingMode;
        $this->resolution = $resolution;
        $this->referencedPersonFactory = $referencedPersonFactory;
    }

    protected function createComponent(Field $field, BaseMachine $machine, Container $container) {
        $searchType = $this->evalParam($this->searchType);
        $allowClear = $this->evalParam($this->allowClear);
        $fillingMode = $this->evalParam($this->fillingMode);
        $resolution = $this->evalParam($this->resolution);

        $event = $field->getBaseHolder()->getHolder()->getEvent();
        $acYear = $event->event_type->contest->related('contest_year')->where('year', $event->year)->fetch()->ac_year;

        $components = $this->referencedPersonFactory->createReferencedPerson($this->fieldsDefinition, $acYear, $searchType, $allowClear, $fillingMode, $resolution);
        $components[1]->setOption('label', $field->getLabel());
        $components[1]->setOption('description', $field->getDescription());
        return $components;
    }

    protected function setDefaultValue($component, Field $field, BaseMachine $machine, Container $container) {
        $hiddenField = reset($component);
        $hiddenField->setDefaultValue($field->getValue());
    }

    protected function setDisabled($component, Field $field, BaseMachine $machine, Container $container) {
        $hiddenField = reset($component);
        $hiddenField->setDisabled();
    }

    public function getMainControl(Component $component) {
        return $component;
    }

    private function evalParam($param) {
        if (is_object($param)) {
            return $param->__invoke();
        } else {
            return $param;
        }
    }

}

