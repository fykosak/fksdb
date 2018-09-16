<?php

namespace FKSDB\Components\Forms\Factories\Events;

use Events\EventsExtension;
use Events\Machine\BaseMachine;
use Events\Model\ExpressionEvaluator;
use Events\Model\Holder\DataValidator;
use Events\Model\Holder\Field;
use Events\Model\PersonContainerResolver;
use FKS\Config\Expressions\Helpers;
use FKSDB\Components\Forms\Factories\ReferencedPerson\ReferencedEventPersonFactory;
use Nette\ComponentModel\Component;
use Nette\DI\Container as DIContainer;
use Nette\Forms\Container;
use Nette\Forms\Controls\HiddenField;
use Nette\Security\User;
use Persons\SelfResolver;
use ServicePerson;

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
     * @var ReferencedEventPersonFactory
     */
    private $referencedEventPersonFactory;

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

    /**
     * @var ServicePerson
     */
    private $servicePerson;

    /**
     * @var DIContainer
     */
    private $container;

    function __construct($fieldsDefinition, $searchType, $allowClear, $modifiable, $visible, ReferencedEventPersonFactory $referencedEventPersonFactory, SelfResolver $selfResolver, ExpressionEvaluator $evaluator, User $user, ServicePerson $servicePerson, DIContainer $container) {
        $this->fieldsDefinition = $fieldsDefinition;
        $this->searchType = $searchType;
        $this->allowClear = $allowClear;
        $this->modifiable = $modifiable;
        $this->visible = $visible;
        $this->referencedEventPersonFactory = $referencedEventPersonFactory;
        $this->selfResolver = $selfResolver;
        $this->evaluator = $evaluator;
        $this->user = $user;
        $this->servicePerson = $servicePerson;
        $this->container = $container;
    }

    protected function createComponent(Field $field, BaseMachine $machine, Container $container) {
        $searchType = $this->evaluator->evaluate($this->searchType, $field);
        $allowClear = $this->evaluator->evaluate($this->allowClear, $field);

        $event = $field->getBaseHolder()->getEvent();

        $this->referencedEventPersonFactory->setEventId($event->event_id);
        $acYear = $event->getAcYear();

        $modifiableResolver = new PersonContainerResolver($field, $this->modifiable, $this->selfResolver, $this->evaluator);
        $visibleResolver = new PersonContainerResolver($field, $this->visible, $this->selfResolver, $this->evaluator);
        $fieldsDefinition = $this->evaluateFieldsDefinition($field);
        $components = $this->referencedEventPersonFactory->createReferencedPerson($fieldsDefinition, $acYear, $searchType, $allowClear, $modifiableResolver, $visibleResolver);
        $components[1]->setOption('label', $field->getLabel());
        $components[1]->setOption('description', $field->getDescription());
        return $components;
    }

    /**
     * @param HiddenField[] $component
     * @param Field $field
     * @param BaseMachine $machine
     * @param Container $container
     */
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

    public function validate(Field $field, DataValidator $validator) {
        // check person ID itself
        parent::validate($field, $validator);

        $fieldsDefinition = $this->evaluateFieldsDefinition($field);
        $event = $field->getBaseHolder()->getEvent();
        $acYear = $event->getAcYear();
        $personId = $field->getValue();
        $person = $personId ? $this->servicePerson->findByPrimary($personId) : null;

        if (!$person) {
            return;
        }

        foreach ($fieldsDefinition as $subName => $sub) {
            foreach ($sub as $fieldName => $metadata) {
                if (!is_array($metadata)) {
                    $metadata = array('required' => $metadata);
                }
                if ($metadata['required'] && !$this->referencedEventPersonFactory->isFilled($person, $subName, $fieldName, $acYear)) {
                    $validator->addError(sprintf(_('%s: %s je povinná položka.'), $field->getBaseHolder()->getLabel(), $field->getLabel() . '.' . $subName . '.' . $fieldName)); //TODO better GUI name than DB identifier
                }
            }
        }
    }

    private function evaluateFieldsDefinition(Field $field) {
        Helpers::registerSemantic(EventsExtension::$semanticMap);
        $fieldsDefinition = Helpers::evalExpressionArray($this->fieldsDefinition, $this->container);

        foreach ($fieldsDefinition as &$sub) {
            foreach ($sub as &$metadata) {
                if (!is_array($metadata)) {
                    $metadata = ['required' => $metadata];
                }
                foreach ($metadata as &$value) {
                    $value = $this->evaluator->evaluate($value, $field);
                }
            }
        }

        return $fieldsDefinition;
    }

}

