<?php

namespace FKSDB\Components\Forms\Factories\Events;

use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\Components\Forms\Factories\ReferencedPerson\ReferencedPersonFactory;
use FKSDB\Models\Events\EventsExtension;
use FKSDB\Models\Events\Model\ExpressionEvaluator;
use FKSDB\Models\Events\Model\Holder\DataValidator;
use FKSDB\Models\Events\Model\Holder\Field;
use FKSDB\Models\Events\Model\PersonContainerResolver;
use FKSDB\Models\Expressions\Helpers;
use FKSDB\Models\ORM\Services\ServicePerson;
use FKSDB\Models\Persons\SelfResolver;
use Nette\DI\Container as DIContainer;
use Nette\Forms\Controls\BaseControl;
use Nette\Security\User;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class PersonFactory extends AbstractFactory {

    private const VALUE_LOGIN = 'fromLogin';
    /** @var callable */
    private $fieldsDefinition;
    /** @var callable */
    private $searchType;
    /** @var callable */
    private $allowClear;
    /** @var callable */
    private $modifiable;
    /** @var callable */
    private $visible;
    private ReferencedPersonFactory $referencedPersonFactory;
    private SelfResolver $selfResolver;
    private ExpressionEvaluator $evaluator;
    private User $user;
    private ServicePerson $servicePerson;
    private DIContainer $container;

    /**
     * PersonFactory constructor.
     * @param callable|array $fieldsDefinition
     * @param callable|string $searchType
     * @param callable|bool $allowClear
     * @param callable|bool $modifiable
     * @param callable|bool $visible
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

    protected function setDefaultValue(BaseControl $control, Field $field): void {
        $default = $field->getValue();
        if ($default == self::VALUE_LOGIN) {
            if ($this->user->isLoggedIn() && $this->user->getIdentity()->getPerson()) {
                $default = $this->user->getIdentity()->getPerson()->person_id;
            } else {
                $default = null;
            }
        }
        $control->setDefaultValue($default);
    }

    /**
     * @param Field $field
     * @param DataValidator $validator
     * @return void
     * @throws \ReflectionException
     */
    public function validate(Field $field, DataValidator $validator): void {
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
     * @return array
     * @throws \ReflectionException
     */
    private function evaluateFieldsDefinition(Field $field): array {
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
