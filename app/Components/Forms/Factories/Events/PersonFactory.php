<?php

namespace FKSDB\Components\Forms\Factories\Events;

use Events\Machine\BaseMachine;
use Events\Model\Field;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Forms\Factories\PersonFactory as CorePersonFactory;
use Nette\ComponentModel\Component;
use Nette\Forms\Container;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class PersonFactory extends AbstractFactory {

    private $fieldsDefinition;

    /**
     * @var CorePersonFactory
     */
    private $personFactory;

    /**
     * @var PersonProvider
     */
    private $personProvider;
    private $searchType = CorePersonFactory::SEARCH_EMAIL;
    private $allowClear = true; //TODO depends on is logged in and outer settings
    private $filledFields = CorePersonFactory::EX_DISABLED; //TODO depends on is logged in and outer settings

    function __construct($fieldsDefinition, CorePersonFactory $personFactory, PersonProvider $personProvider) {
        $this->fieldsDefinition = $fieldsDefinition;
        $this->personFactory = $personFactory;
        $this->personProvider = $personProvider;
    }

    protected function createComponent(Field $field, BaseMachine $machine, Container $container) {
        $event = $field->getBaseHolder()->getHolder()->getEvent();
        $acYear = $event->event_type->contest->related('contest_year')->where('year', $event->year)->fetch()->ac_year;
        return $this->personFactory->createDynamicPerson($this->searchType, $this->allowClear, $this->filledFields, $this->fieldsDefinition, $acYear, $this->personProvider);
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

}

