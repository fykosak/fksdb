<?php

namespace FKSDB\Components\Forms\Factories\Events;

use FKSDB\Events\EventsExtension;
use FKSDB\Events\Machine\BaseMachine;
use FKSDB\Events\Model\ExpressionEvaluator;
use FKSDB\Events\Model\Holder\DataValidator;
use FKSDB\Events\Model\Holder\Field;
use FKSDB\Events\Model\PersonContainerResolver;
use FKSDB\Components\Forms\Factories\ReferencedPerson\ReferencedEventPersonFactory;
use FKSDB\Config\Expressions\Helpers;
use FKSDB\ORM\Services\ServicePerson;
use Nette\ComponentModel\Component;
use Nette\DI\Container as DIContainer;
use Nette\Forms\Container;
use Nette\Forms\IControl;
use Nette\Security\User;
use Persons\SelfResolver;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class PersonFactory extends AbstractFactory {

    const VALUE_LOGIN = 'fromLogin';

    /**
     * @var array
     */
    private $fieldsDefinition;
    /**
     * @var string
     */
    private $searchType;
    /**
     * @var bool
     */
    private $allowClear;
    /**
     * @var bool
     */
    private $modifiable;
    /**
     * @var bool
     */
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

    /**
     * PersonFactory constructor.
     * @param array $fieldsDefinition
     * @param string $searchType
     * @param bool $allowClear
     * @param $modifiable
     * @param $visible
     * @param ReferencedEventPersonFactory $referencedEventPersonFactory
     * @param SelfResolver $selfResolver
     * @param ExpressionEvaluator $evaluator
     * @param User $user
     * @param ServicePerson $servicePerson
     * @param DIContainer $container
     */
    public function __construct(
        array $fieldsDefinition,
        string $searchType,
        bool $allowClear,
        $modifiable,
        $visible,
        ReferencedEventPersonFactory $referencedEventPersonFactory,
        SelfResolver $selfResolver,
        ExpressionEvaluator $evaluator,
        User $user,
        ServicePerson $servicePerson,
        DIContainer $container
    ) {
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

    /**
     * @param Field $field
     * @param Container $container
     * @return IControl[]
     * @throws \Exception
     */
    protected function createComponent(Field $field, Container $container) {
        $searchType = $this->evaluator->evaluate($this->searchType, $field);
        $allowClear = $this->evaluator->evaluate($this->allowClear, $field);

        $event = $field->getBaseHolder()->getEvent();
        $this->referencedEventPersonFactory->setEvent($event);
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
     * @param IControl[] $component
     * @param Field $field
     * @param Container $container
     */
    protected function setDefaultValue($component, Field $field, Container $container) {
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

    /**
     * @param IControl[] $component
     * @param Field $field
     * @param Container $container
     * @return void
     */
    protected function setDisabled($component, Field $field, Container $container) {
        $hiddenField = reset($component);
        $hiddenField->setDisabled();
    }

    /**
     * @param Component $component
     * @return Component|IControl
     */
    public function getMainControl(Component $component) {
        return $component;
    }

    /**
     * @param Field $field
     * @param DataValidator $validator
     * @return bool|void
     */
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
                    $metadata = ['required' => $metadata];
                }
                if ($metadata['required'] && !$this->referencedEventPersonFactory->isFilled($person, $subName, $fieldName, $acYear)) {
                    $validator->addError(sprintf(_('%s: %s je povinná položka.'), $field->getBaseHolder()->getLabel(), $field->getLabel() . '.' . $subName . '.' . $fieldName)); //TODO better GUI name than DB identifier
                }
            }
        }
    }

    /**
     * @param Field $field
     * @return array|mixed
     */
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
