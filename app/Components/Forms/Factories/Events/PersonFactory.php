<?php

namespace FKSDB\Components\Forms\Factories\Events;

use Events\Machine\BaseMachine;
use Events\Model\ExpressionEvaluator;
use Events\Model\Holder\Field;
use Events\Model\PersonContainerResolver;
use FKSDB\Components\Forms\Factories\ReferencedPersonFactory;
use Nette\ComponentModel\Component;
use Nette\Forms\Container;
use Nette\Security\User;
use Persons\SelfResolver;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class PersonFactory extends AbstractFactory {

    const VALUE_LOGIN = 'fromLogin';

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
     * @var ExpressionEvaluator
     */
    private $evaluator;

    /**
     * @var User
     */
    private $user;

    function __construct($fieldsDefinition, $searchType, $allowClear, $modifiable, $visible, ReferencedPersonFactory $referencedPersonFactory, SelfResolver $selfResolver, ExpressionEvaluator $evaluator, User $user) {
        $this->fieldsDefinition = $fieldsDefinition;
        $this->searchType = $searchType;
        $this->allowClear = $allowClear;
        $this->modifiable = $modifiable;
        $this->visible = $visible;
        $this->referencedPersonFactory = $referencedPersonFactory;
        $this->selfResolver = $selfResolver;
        $this->evaluator = $evaluator;
        $this->user = $user;
    }

    protected function createComponent(Field $field, BaseMachine $machine, Container $container) {
        $searchType = $this->evaluator->evaluate($this->searchType, $field);
        $allowClear = $this->evaluator->evaluate($this->allowClear, $field);

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
        $default = $field->getValue();
        if ($default == self::VALUE_LOGIN) {
            if ($this->user->isLoggedIn() && $this->user->getIdentity()->getPerson()) {
                $default = $this->user->getIdentity()->getPerson()->person_id;
            } else {
                $default = null;
            }
        }

        $hiddenField->setDefaultValue($default);
    }

    protected function setDisabled($component, Field $field, BaseMachine $machine, Container $container) {
        $hiddenField = reset($component);
        $hiddenField->setDisabled();
    }

    public function getMainControl(Component $component) {
        return $component;
    }

}

