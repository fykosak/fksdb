<?php

namespace FKSDB\Components\Forms\Factories\Events;

use Events\Machine\BaseMachine;
use Events\Model\ConditionEvaluator;
use Events\Model\Holder\Field;
use Events\Model\PersonContainerResolver;
use FKSDB\Components\Forms\Factories\ReferencedPersonFactory;
use Nette\ComponentModel\Component;
use Nette\Forms\Container;
use Persons\SelfResolver;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class PersonFactory extends AbstractFactory {

    private $fieldsDefinition;
    private $searchType;
    private $allowClear;
    private $modifiable;
    private $visible;

    /**
     * @var ReferencedPersonFactory
     */
    private $referencedPersonFactory;

    /**
     * @var SelfResolver
     */
    private $selfResolver;

    /**
     * @var ConditionEvaluator
     */
    private $evaluator;

    function __construct($fieldsDefinition, $searchType, $allowClear, $modifiable, $visible, ReferencedPersonFactory $referencedPersonFactory, SelfResolver $selfResolver, ConditionEvaluator $evaluator) {
        $this->fieldsDefinition = $fieldsDefinition;
        $this->searchType = $searchType;
        $this->allowClear = $allowClear;
        $this->modifiable = $modifiable;
        $this->visible = $visible;
        $this->referencedPersonFactory = $referencedPersonFactory;
        $this->selfResolver = $selfResolver;
        $this->evaluator = $evaluator;
    }

    protected function createComponent(Field $field, BaseMachine $machine, Container $container) {
        $searchType = $this->evalParam($this->searchType);
        $allowClear = $this->evalParam($this->allowClear);

        $event = $field->getBaseHolder()->getHolder()->getEvent();
        $acYear = $event->getAcYear();

        $modifiableResolver = new PersonContainerResolver($field, $this->modifiable, $this->selfResolver, $this->evaluator);
        $visibleResolver = new PersonContainerResolver($field, $this->visible, $this->selfResolver, $this->evaluator);
        $components = $this->referencedPersonFactory->createReferencedPerson($this->fieldsDefinition, $acYear, $searchType, $allowClear, $modifiableResolver, $visibleResolver);
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

