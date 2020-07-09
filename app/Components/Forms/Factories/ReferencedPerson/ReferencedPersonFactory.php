<?php

namespace FKSDB\Components\Forms\Factories\ReferencedPerson;

use Closure;
use FKSDB\Components\DatabaseReflection\ColumnFactories\AbstractColumnException;
use FKSDB\Components\DatabaseReflection\OmittedControlException;
use FKSDB\Components\Forms\Containers\AddressContainer;
use FKSDB\Components\Forms\Containers\IWriteOnly;
use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Components\Forms\Containers\Models\IReferencedSetter;
use FKSDB\Components\Forms\Containers\Models\ReferencedContainer;
use FKSDB\Components\Forms\Containers\Models\ReferencedPersonContainer;
use FKSDB\Components\Forms\Containers\SearchContainer\PersonSearchContainer;
use FKSDB\Components\Forms\Controls\Autocomplete\AutocompleteSelectBox;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\Components\Forms\Factories\AddressFactory;
use FKSDB\Components\Forms\Factories\FlagFactory;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\Components\Forms\Factories\PersonScheduleFactory;
use FKSDB\Components\Forms\Factories\SingleReflectionFormFactory;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Exceptions\NotImplementedException;
use FKSDB\ORM\IModel;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Models\ModelPostContact;
use FKSDB\ORM\Services\ServiceFlag;
use FKSDB\ORM\Services\ServicePerson;
use Nette\Application\BadRequestException;
use Nette\ComponentModel\IComponent;
use Nette\Forms\Container;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\HiddenField;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\InvalidArgumentException;
use Nette\InvalidStateException;
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

    const SEARCH_EMAIL = 'email';
    const SEARCH_ID = 'id';
    const SEARCH_NONE = 'none';
    const TARGET_FORM = 0x1;
    const TARGET_VALIDATION = 0x2;
    const EXTRAPOLATE = 0x4;
    const HAS_DELIVERY = 0x8;

    /**
     * @var ServicePerson
     */
    protected $servicePerson;

    /**
     * @var PersonFactory
     */
    protected $personFactory;

    /**
     * @var SingleReflectionFormFactory
     */
    protected $singleReflectionFormFactory;

    /**
     * @var ReferencedPersonHandlerFactory
     */
    protected $referencedPersonHandlerFactory;

    /**
     * @var PersonProvider
     */
    protected $personProvider;

    /**
     * @var ServiceFlag
     */
    protected $serviceFlag;

    /**
     * @var FlagFactory
     */
    protected $flagFactory;
    /**
     * @var AddressFactory
     */
    protected $addressFactory;
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
     * @param AddressFactory $addressFactory
     * @param FlagFactory $flagFactory
     * @param ServicePerson $servicePerson
     * @param PersonFactory $personFactory
     * @param ReferencedPersonHandlerFactory $referencedPersonHandlerFactory
     * @param PersonProvider $personProvider
     * @param ServiceFlag $serviceFlag
     * @param SingleReflectionFormFactory $singleReflectionFormFactory
     * @param PersonScheduleFactory $personScheduleFactory
     * @param \Nette\DI\Container $context
     */
    public function __construct(
        AddressFactory $addressFactory,
        FlagFactory $flagFactory,
        ServicePerson $servicePerson,
        PersonFactory $personFactory,
        ReferencedPersonHandlerFactory $referencedPersonHandlerFactory,
        PersonProvider $personProvider,
        ServiceFlag $serviceFlag,
        SingleReflectionFormFactory $singleReflectionFormFactory,
        PersonScheduleFactory $personScheduleFactory,
        \Nette\DI\Container $context
    ) {
        $this->servicePerson = $servicePerson;
        $this->personFactory = $personFactory;
        $this->referencedPersonHandlerFactory = $referencedPersonHandlerFactory;
        $this->personProvider = $personProvider;
        $this->serviceFlag = $serviceFlag;
        $this->singleReflectionFormFactory = $singleReflectionFormFactory;
        $this->flagFactory = $flagFactory;
        $this->addressFactory = $addressFactory;
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
     * @throws AbstractColumnException
     * @throws BadRequestException
     * @throws BadTypeException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws OmittedControlException
     */
    public function createReferencedPerson(
        array $fieldsDefinition,
        int $acYear,
        string $searchType,
        bool $allowClear,
        IModifiabilityResolver $modifiabilityResolver,
        IVisibilityResolver $visibilityResolver,
        $event = null): ReferencedId {
        $handler = $this->referencedPersonHandlerFactory->create($acYear, null, $event ?? null);

        $hiddenField = new ReferencedId(
            new PersonSearchContainer($this->context, $searchType),
            new ReferencedPersonContainer($this->context, $modifiabilityResolver, $visibilityResolver, $acYear, $fieldsDefinition, $event, $allowClear),
            $this->servicePerson,
            $handler,
            $this
        );
        /** @var ReferencedPersonContainer $container */
        $container = $hiddenField->getReferencedContainer();

        $container->setAllowClear($allowClear);
        $container->setOption('acYear', $acYear);
        $container->modifiabilityResolver = $modifiabilityResolver;
        $container->visibilityResolver = $visibilityResolver;

        foreach ($fieldsDefinition as $sub => $fields) {
            $subContainer = new ContainerWithOptions();
            if ($sub == ReferencedPersonHandler::POST_CONTACT_DELIVERY) {
                $subContainer->setOption('showGroup', true);
                $subContainer->setOption('label', _('Doručovací adresa'));
            } elseif ($sub == ReferencedPersonHandler::POST_CONTACT_PERMANENT) {
                $subContainer->setOption('showGroup', true);
                $label = _('Trvalá adresa');
                if (isset($container[ReferencedPersonHandler::POST_CONTACT_DELIVERY])) {
                    $label .= ' ' . _('(je-li odlišná od doručovací)');
                }
                $subContainer->setOption('label', $label);
            }
            foreach ($fields as $fieldName => $metadata) {
                $control = $this->createField($sub, $fieldName, $acYear, $hiddenField, $metadata, $event);
                $fullFieldName = "$sub.$fieldName";
                if ($handler->isSecondaryKey($fullFieldName)) {
                    if ($fieldName != 'email') {
                        throw new InvalidStateException("Should define uniqueness validator for field $sub.$fieldName.");
                    }

                    $control->addCondition(function () use ($hiddenField) { // we use this workaround not to call getValue inside validation out of transaction
                        $personId = $hiddenField->getValue(false);
                        return $personId && $personId != ReferencedId::VALUE_PROMISE;
                    })
                        ->addRule(function (BaseControl $control) use ($fullFieldName, $hiddenField, $handler) {
                            $personId = $hiddenField->getValue(false);

                            $foundPerson = $handler->findBySecondaryKey($fullFieldName, $control->getValue());
                            if ($foundPerson && $foundPerson->getPrimary() != $personId) {
                                $hiddenField->setValue($foundPerson, ReferencedId::MODE_FORCE);
                                return false;
                            }
                            return true;
                        }, _('S e-mailem %value byla nalezena (formálně) jiná (ale pravděpodobně duplicitní) osoba, a tak ve formuláři nahradila původní.'));
                }

                $subContainer->addComponent($control, $fieldName);
            }
            $container->addComponent($subContainer, $sub);
        }

        return $hiddenField;

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
        $acYear = $container->getOption('acYear');
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
                    $options = self::TARGET_FORM | self::HAS_DELIVERY;
                } else {
                    $options = self::TARGET_FORM;
                }
                $realValue = $this->getPersonValue($model, $sub, $fieldName, $acYear, $options, $event); // not extrapolated
                $value = $this->getPersonValue($model, $sub, $fieldName, $acYear, $options | self::EXTRAPOLATE, $event);
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
     * @param string $sub
     * @param string $fieldName
     * @param int $acYear
     * @param HiddenField $hiddenField
     * @param array $metadata
     * @param ModelEvent|null $event
     * @return IComponent|AddressContainer|BaseControl
     * @throws AbstractColumnException
     * @throws BadRequestException
     * @throws BadTypeException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws OmittedControlException
     */
    public function createField(string $sub, string $fieldName, int $acYear, HiddenField $hiddenField, array $metadata, $event = null): IComponent {
        if (in_array($sub, [
            ReferencedPersonHandler::POST_CONTACT_DELIVERY,
            ReferencedPersonHandler::POST_CONTACT_PERMANENT,
        ])) {
            if ($fieldName == 'address') {
                $required = (bool)$metadata['required'] ?? false;
                if ($required) {
                    $options = AddressFactory::REQUIRED;
                } else {
                    $options = 0;
                }
                return $this->addressFactory->createAddress($options, $hiddenField);
            } else {
                throw new InvalidArgumentException("Only 'address' field is supported.");
            }
        } elseif ($sub == 'person_has_flag') {
            return $this->flagFactory->createFlag($hiddenField, $metadata);
        } else {
            $control = null;
            switch ($sub) {
                case 'person_schedule':
                    $control = $this->personScheduleFactory->createField($fieldName, $event);
                    break;
                case 'person':
                case 'person_info':
                    $control = $this->singleReflectionFormFactory->createField($sub, $fieldName);
                    break;
                case 'person_history':
                    $control = $this->singleReflectionFormFactory->createField($sub, $fieldName, $acYear);
                    break;
                default:
                    throw new InvalidArgumentException();

            }
            $this->appendMetadata($control, $hiddenField, $fieldName, $metadata);

            return $control;
        }
    }

    /**
     * @param BaseControl $control
     * @param HiddenField $hiddenField
     * @param string $fieldName
     * @param array $metadata
     * @return void
     */
    protected function appendMetadata(BaseControl $control, HiddenField $hiddenField, string $fieldName, array $metadata) {
        foreach ($metadata as $key => $value) {
            switch ($key) {
                case 'required':
                    if ($value) {
                        $conditioned = $control->addConditionOn($hiddenField, Form::FILLED);

                        if ($fieldName == 'agreed') { // NOTE: this may need refactoring when more customization requirements occurre
                            $conditioned->addRule(Form::FILLED, _('Bez souhlasu nelze bohužel pokračovat.'));
                        } else {
                            $conditioned->addRule(Form::FILLED, _('Pole %label je povinné.'));
                        }
                    }
                    break;
                case 'caption':
                    if ($value) {
                        $control->caption = $value;
                    }
                    break;
                case 'description':
                    if ($value) {
                        $control->setOption('description', $value);
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
     * @param string $searchType
     * @return AutocompleteSelectBox|TextInput
     */
    protected function createSearchControl(string $searchType) {

        switch ($searchType) {
            case self::SEARCH_EMAIL:
                $control = new TextInput(_('E-mail'));
                $control->addCondition(Form::FILLED)
                    ->addRule(Form::EMAIL, _('Neplatný tvar e-mailu.'));
                $control->setOption('description', _('Nejprve zkuste najít osobu v naší databázi podle e-mailu.'));
                $control->setAttribute('placeholder', 'your-email@exmaple.com');
                $control->setAttribute('autocomplete', 'email');
                break;
            case self::SEARCH_ID:
                $control = $this->personFactory->createPersonSelect(true, _('Person'), $this->personProvider);
                break;
            default:
                throw new InvalidArgumentException(_('Unknown search type'));
        }
        return $control;
    }

    protected function createSearchCallback(string $searchType): Closure {
        switch ($searchType) {
            case self::SEARCH_EMAIL:
                return function ($term) {
                    return $this->servicePerson->findByEmail($term);
                };
            case self::SEARCH_ID:
                return function ($term) {
                    return $this->servicePerson->findByPrimary($term);
                };
            default:
                throw new InvalidArgumentException(_('Unknown search type'));
        }
    }

    protected function createTermToValuesCallback(string $searchType): Closure {
        switch ($searchType) {
            case self::SEARCH_EMAIL:
                return function ($term) {
                    return ['person_info' => ['email' => $term]];
                };
                break;
            case self::SEARCH_ID:
                return function () {
                    return [];
                };
            default:
                throw new InvalidArgumentException(_('Unknown search type'));
        }
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
        $value = $this->getPersonValue($person, $sub, $field, $acYear, self::TARGET_VALIDATION);
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
                return ($history = $person->getHistory($acYear, (bool)($options & self::EXTRAPOLATE))) ? $history[$field] : null;
            case 'post_contact_d':
                return $person->getDeliveryAddress();
            case 'post_contact_p':
                if (($options & self::TARGET_VALIDATION) || !($options & self::HAS_DELIVERY)) {
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
