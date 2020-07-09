<?php

namespace FKSDB\Components\Forms\Factories\ReferencedPerson;

use FKSDB\Components\Forms\Containers\IWriteOnly;
use FKSDB\Components\Forms\Containers\Models\IReferencedSetter;
use FKSDB\Components\Forms\Containers\Models\ReferencedContainer;
use FKSDB\Components\Forms\Containers\Models\ReferencedPersonContainer;
use FKSDB\Components\Forms\Containers\SearchContainer\PersonSearchContainer;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\Components\Forms\Factories\PersonScheduleFactory;
use FKSDB\ORM\IModel;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Models\ModelPostContact;
use FKSDB\ORM\Services\ServicePerson;
use Nette\ComponentModel\IComponent;
use Nette\Forms\Container;
use Nette\Forms\Controls\BaseControl;
use Nette\InvalidArgumentException;
use Nette\SmartObject;
use Nette\Utils\JsonException;
use Persons\IModifiabilityResolver;
use Persons\IVisibilityResolver;
use Persons\ReferencedPersonHandler;
use Persons\ReferencedPersonHandlerFactory;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class ReferencedPersonFactory implements IReferencedSetter {
    use SmartObject;

    /**
     * @var ServicePerson
     */
    protected $servicePerson;

    /**
     * @var PersonFactory
     */
    protected $personFactory;


    /**
     * @var ReferencedPersonHandlerFactory
     */
    protected $referencedPersonHandlerFactory;

    /**
     * @var PersonProvider
     */
    protected $personProvider;

    /**
     * @var PersonScheduleFactory
     */
    private $personScheduleFactory;
    /**
     * @var ModelEvent
     */
    private $event;
    /** @var \Nette\DI\Container */
    private $context;

    /**
     * AbstractReferencedPersonFactory constructor.
     * @param ServicePerson $servicePerson
     * @param PersonFactory $personFactory
     * @param ReferencedPersonHandlerFactory $referencedPersonHandlerFactory
     * @param PersonProvider $personProvider
     * @param PersonScheduleFactory $personScheduleFactory
     * @param \Nette\DI\Container $context
     */
    public function __construct(
        ServicePerson $servicePerson,
        PersonFactory $personFactory,
        ReferencedPersonHandlerFactory $referencedPersonHandlerFactory,
        PersonProvider $personProvider,
        PersonScheduleFactory $personScheduleFactory,
        \Nette\DI\Container $context
    ) {
        $this->servicePerson = $servicePerson;
        $this->personFactory = $personFactory;
        $this->referencedPersonHandlerFactory = $referencedPersonHandlerFactory;
        $this->personProvider = $personProvider;
        $this->personScheduleFactory = $personScheduleFactory;
        $this->context = $context;
    }

    /**
     * @param array $fieldsDefinition
     * @param int $acYear
     * @param string $searchType
     * @param bool $allowClear
     * @param IModifiabilityResolver $modifiabilityResolver
     * @param IVisibilityResolver $visibilityResolver
     * @param ModelEvent|null $event
     * @return ReferencedId
     */
    public function createReferencedPerson(
        array $fieldsDefinition,
        int $acYear,
        string $searchType,
        bool $allowClear,
        IModifiabilityResolver $modifiabilityResolver,
        IVisibilityResolver $visibilityResolver,
        $event = null
    ): ReferencedId {

        $handler = $this->referencedPersonHandlerFactory->create($acYear, null, $event);
        return new ReferencedId(
            new PersonSearchContainer($this->context, $searchType),
            new ReferencedPersonContainer($this->context, $modifiabilityResolver, $visibilityResolver, $acYear, $fieldsDefinition, $event, $allowClear),
            $this->servicePerson,
            $handler,
            $this
        );
    }

    /**
     * @param ReferencedContainer|ReferencedPersonContainer $container
     * @param IModel|ModelPerson|null $model
     * @param string $mode
     * @param ModelEvent|null $event
     * @return void
     * @throws JsonException
     */
    public function setModel(ReferencedContainer $container, IModel $model = null, string $mode = ReferencedId::MODE_NORMAL, $event = null) {
        $modifiable = $model ? $container->modifiabilityResolver->isModifiable($model) : true;
        $resolution = $model ? $container->modifiabilityResolver->getResolutionMode($model) : ReferencedPersonHandler::RESOLUTION_OVERWRITE;
        $visible = $model ? $container->visibilityResolver->isVisible($model) : true;
        $submittedBySearch = $container->getReferencedId()->getSearchContainer()->isSearchSubmitted();
        $force = ($mode === ReferencedId::MODE_FORCE);
        if ($mode === ReferencedId::MODE_ROLLBACK) {
            $model = null;
        }
        $container->getReferencedId()->getHandler()->setResolution($resolution);

        $container->getComponent(ReferencedContainer::CONTROL_COMPACT)->setValue($model ? $model->getFullName() : null);
        foreach ($container->getComponents() as $sub => $subContainer) {
            if (!$subContainer instanceof Container) {
                continue;
            }
            /**
             * @var string $fieldName
             * @var BaseControl $component
             * TODO type safe
             */
            foreach ($subContainer->getComponents() as $fieldName => $component) {
                if (isset($container[ReferencedPersonHandler::POST_CONTACT_DELIVERY])) {
                    $options = ReferencedPersonContainer::TARGET_FORM | ReferencedPersonContainer::HAS_DELIVERY;
                } else {
                    $options = ReferencedPersonContainer::TARGET_FORM;
                }
                $realValue = $this->getPersonValue($model, $sub, $fieldName, $container->acYear, $options, $event); // not extrapolated
                $value = $this->getPersonValue($model, $sub, $fieldName, $container->acYear, $options | ReferencedPersonContainer::EXTRAPOLATE, $event);
                $controlModifiable = ($realValue !== null) ? $modifiable : true;
                $controlVisible = $this->isWriteOnly($component) ? $visible : true;

                if (!$controlVisible && !$controlModifiable) {
                    $container[$sub]->removeComponent($component);
                } elseif (!$controlVisible && $controlModifiable) {
                    $this->setWriteOnly($component, true);
                    $component->setDisabled(false);
                } elseif ($controlVisible && !$controlModifiable) {
                    $component->setDisabled();
                    $component->setValue($value);
                } elseif ($controlVisible && $controlModifiable) {
                    $this->setWriteOnly($component, false);
                    $component->setDisabled(false);
                }
                if ($mode == ReferencedId::MODE_ROLLBACK) {
                    $component->setDisabled(false);
                    $this->setWriteOnly($component, false);
                } else {
                    if ($submittedBySearch || $force) {
                        $component->setValue($value);
                    } else {
                        $component->setDefaultValue($value);
                    }
                    if ($realValue && $resolution == ReferencedPersonHandler::RESOLUTION_EXCEPTION) {
                        $component->setDisabled(); // could not store different value anyway
                    }
                }
            }
        }
    }

    /**
     * @param IComponent $component
     * @param bool $value
     * @return void
     */
    protected function setWriteOnly(IComponent $component, bool $value) {
        if ($component instanceof IWriteOnly) {
            $component->setWriteOnly($value);
        } elseif ($component instanceof Container) {
            foreach ($component->getComponents() as $subComponent) {
                $this->setWriteOnly($subComponent, $value);
            }
        }
    }

    protected function isWriteOnly(IComponent $component): bool {
        if ($component instanceof IWriteOnly) {
            return true;
        } elseif ($component instanceof Container) {
            foreach ($component->getComponents() as $subComponent) {
                if ($this->isWriteOnly($subComponent)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param ModelPerson $person
     * @param string $sub
     * @param string $field
     * @param int $acYear
     * @return bool
     * @throws JsonException
     */
    final public function isFilled(ModelPerson $person, string $sub, string $field, int $acYear): bool {
        $value = $this->getPersonValue($person, $sub, $field, $acYear, ReferencedPersonContainer::TARGET_VALIDATION);
        return !($value === null || $value === '');
    }

    /**
     * @param ModelPerson|null $person
     * @param string $sub
     * @param string $field
     * @param int $acYear
     * @param int $options
     * @param ModelEvent|null $event
     * @return bool|ModelPostContact|mixed|null
     * @throws JsonException
     */
    protected function getPersonValue($person, string $sub, string $field, int $acYear, $options, $event = null) {
        if (!$person) {
            return null;
        }
        switch ($sub) {
            case 'person_schedule':
                return $person->getSerializedSchedule($event->event_id, $field);
            case 'person':
                return $person[$field];
            case 'person_info':
                $result = ($info = $person->getInfo()) ? $info[$field] : null;
                if ($field == 'agreed') {
                    // See isFilled() semantics. We consider those who didn't agree as NOT filled.
                    $result = $result ? true : null;
                }
                return $result;
            case 'person_history':
                return ($history = $person->getHistory($acYear, (bool)($options & ReferencedPersonContainer::EXTRAPOLATE))) ? $history[$field] : null;
            case 'post_contact_d':
                return $person->getDeliveryAddress();
            case 'post_contact_p':
                if (($options & ReferencedPersonContainer::TARGET_VALIDATION) || !($options & ReferencedPersonContainer::HAS_DELIVERY)) {
                    return $person->getPermanentAddress();
                }
                return $person->getPermanentAddress(true);
            case 'person_has_flag':
                return ($flag = $person->getMPersonHasFlag($field)) ? (bool)$flag['value'] : null;
            default:
                throw new InvalidArgumentException("Unknown person sub '$sub'.");
        }
    }
}
