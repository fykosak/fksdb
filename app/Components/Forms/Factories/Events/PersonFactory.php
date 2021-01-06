<?php

namespace FKSDB\Components\Forms\Factories\Events;

use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\Models\Events\EventsExtension;
use FKSDB\Models\Events\Model\ExpressionEvaluator;
use FKSDB\Models\Events\Model\Holder\DataValidator;
use FKSDB\Models\Events\Model\Holder\Field;
use FKSDB\Models\Events\Model\PersonContainerResolver;
use FKSDB\Components\Forms\Factories\ReferencedPerson\ReferencedPersonFactory;
use FKSDB\Models\Expressions\Helpers;
use FKSDB\Models\ORM\Services\ServicePerson;
use Nette\ComponentModel\Component;
use Nette\ComponentModel\IComponent;
use Nette\DI\Container as DIContainer;
use Nette\Forms\IControl;
use Nette\Security\User;
use FKSDB\Models\Persons\SelfResolver;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class PersonFactory extends AbstractFactory {

    private const VALUE_LOGIN = 'fromLogin';

    /** @var mixed */
    private $fieldsDefinition;
    /** @var mixed */
    private $searchType;
    /** @var mixed */
    private $allowClear;
    /** @var mixed */
    private $modifiable;
    /** @var mixed */
    private $visible;

    private ReferencedPersonFactory $referencedPersonFactory;

    private SelfResolver $selfResolver;

    private ExpressionEvaluator $evaluator;

    private User $user;

    private ServicePerson $servicePerson;

    private DIContainer $container;

    /**
     * PersonFactory constructor.
     * @param array $fieldsDefinition
     * @param string $searchType
     * @param bool $allowClear
     * @param bool $modifiable
     * @param bool $visible
     * @param ReferencedPersonFactory $referencedPersonFactory
     * @param SelfResolver $selfResolver
     * @param ExpressionEvaluator $evaluator
     * @param User $user
     * @param ServicePerson $servicePerson
     * @param DIContainer $container
     */
    public function __construct(
        $fieldsDefinition,
        $searchType,
        $allowClear,
        $modifiable,
        $visible,
        ReferencedPersonFactory $referencedPersonFactory,
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
        $this->referencedPersonFactory = $referencedPersonFactory;
        $this->selfResolver = $selfResolver;
        $this->evaluator = $evaluator;
        $this->user = $user;
        $this->servicePerson = $servicePerson;
        $this->container = $container;
    }

    /**
     * @param Field $field
     * @return ReferencedId
     * @throws \ReflectionException
     */
    public function createComponent(Field $field): ReferencedId {
        $searchType = $this->evaluator->evaluate($this->searchType, $field);
        $allowClear = $this->evaluator->evaluate($this->allowClear, $field);

        $event = $field->getBaseHolder()->getEvent();

        $modifiableResolver = new PersonContainerResolver($field, $this->modifiable, $this->selfResolver, $this->evaluator);
        $visibleResolver = new PersonContainerResolver($field, $this->visible, $this->selfResolver, $this->evaluator);
        $fieldsDefinition = $this->evaluateFieldsDefinition($field);
        $referencedId = $this->referencedPersonFactory->createReferencedPerson($fieldsDefinition, $event->getAcYear(), $searchType, $allowClear, $modifiableResolver, $visibleResolver, $event);
        $referencedId->getReferencedContainer()->setOption('label', $field->getLabel());
        $referencedId->getReferencedContainer()->setOption('description', $field->getDescription());
        return $referencedId;
    }

    /**
     * @param ReferencedId|IComponent $component
     * @param Field $field
     */
    protected function setDefaultValue(IComponent $component, Field $field): void {
        $default = $field->getValue();
        if ($default == self::VALUE_LOGIN) {
            if ($this->user->isLoggedIn() && $this->user->getIdentity()->getPerson()) {
                $default = $this->user->getIdentity()->getPerson()->person_id;
            } else {
                $default = null;
            }
        }
        $component->setDefaultValue($default);
    }

    /**
     * @param ReferencedId|IComponent $component
     * @return void
     */
    protected function setDisabled(IComponent $component): void {
        $component->setDisabled();
    }

    /**
     * @param ReferencedId|IComponent $component
     * @return Component|IControl
     */
    public function getMainControl(IComponent $component): IControl {
        return $component;
    }

    /**
     * @param Field $field
     * @param DataValidator $validator
     * @return bool|void
     * @throws \ReflectionException
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
                if ($metadata['required'] && !ReferencedPersonFactory::isFilled($person, $subName, $fieldName, $acYear)) {
                    $validator->addError(sprintf(_('%s: %s je povinná položka.'), $field->getBaseHolder()->getLabel(), $field->getLabel() . '.' . $subName . '.' . $fieldName)); //TODO better GUI name than DB identifier
                }
            }
        }
    }

    /**
     * @param Field $field
     * @return array|mixed
     * @throws \ReflectionException
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
