<?php

namespace FKSDB\Components\Forms\Factories\ReferencedPerson;

use FKSDB\Components\Forms\Containers\AddressContainer;
use FKSDB\Components\Forms\Containers\IWriteOnly;
use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Components\Forms\Containers\Models\IReferencedSetter;
use FKSDB\Components\Forms\Containers\Models\ReferencedContainer;
use FKSDB\Components\Forms\Controls\Autocomplete\AutocompleteSelectBox;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\Components\Forms\Factories\AddressFactory;
use FKSDB\Components\Forms\Factories\FlagFactory;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\Components\Forms\Factories\PersonHistoryFactory;
use FKSDB\Components\Forms\Factories\PersonInfoFactory;
use FKSDB\ORM\IModel;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Models\ModelPostContact;
use FKSDB\ORM\Services\ServiceFlag;
use FKSDB\ORM\Services\ServicePerson;
use Nette\Forms\Container;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\HiddenField;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\InvalidArgumentException;
use Nette\InvalidStateException;
use Nette\SmartObject;
use Nette\Utils\Arrays;
use Persons\IModifiabilityResolver;
use Persons\IVisibilityResolver;
use Persons\ReferencedPersonHandler;
use Persons\ReferencedPersonHandlerFactory;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class AbstractReferencedPersonFactory implements IReferencedSetter {

    use SmartObject;

    public const SEARCH_EMAIL = 'email';
    public const SEARCH_ID = 'id';
    public const SEARCH_NONE = 'none';
    public const TARGET_FORM = 0x1;
    public const TARGET_VALIDATION = 0x2;
    public const EXTRAPOLATE = 0x4;
    public const HAS_DELIVERY = 0x8;

    protected ServicePerson $servicePerson;

    protected PersonFactory $personFactory;

    protected PersonHistoryFactory $personHistoryFactory;

    protected PersonInfoFactory $personInfoFactory;

    protected ReferencedPersonHandlerFactory $referencedPersonHandlerFactory;

    protected PersonProvider $personProvider;

    protected ServiceFlag $serviceFlag;

    protected FlagFactory $flagFactory;

    protected AddressFactory $addressFactory;

    /**
     * AbstractReferencedPersonFactory constructor.
     * @param AddressFactory $addressFactory
     * @param FlagFactory $flagFactory
     * @param ServicePerson $servicePerson
     * @param PersonFactory $personFactory
     * @param ReferencedPersonHandlerFactory $referencedPersonHandlerFactory
     * @param PersonProvider $personProvider
     * @param ServiceFlag $serviceFlag
     * @param PersonInfoFactory $personInfoFactory
     * @param PersonHistoryFactory $personHistoryFactory
     */
    public function __construct(AddressFactory $addressFactory, FlagFactory $flagFactory, ServicePerson $servicePerson, PersonFactory $personFactory, ReferencedPersonHandlerFactory $referencedPersonHandlerFactory, PersonProvider $personProvider, ServiceFlag $serviceFlag, PersonInfoFactory $personInfoFactory, PersonHistoryFactory $personHistoryFactory) {
        $this->servicePerson = $servicePerson;
        $this->personFactory = $personFactory;
        $this->referencedPersonHandlerFactory = $referencedPersonHandlerFactory;
        $this->personProvider = $personProvider;
        $this->serviceFlag = $serviceFlag;
        $this->personHistoryFactory = $personHistoryFactory;
        $this->personInfoFactory = $personInfoFactory;
        $this->flagFactory = $flagFactory;
        $this->addressFactory = $addressFactory;
    }

    /**
     * @param array $fieldsDefinition
     * @param int $acYear
     * @param string $searchType
     * @param bool $allowClear
     * @param IModifiabilityResolver $modifiabilityResolver is person's filled field modifiable?
     * @param IVisibilityResolver $visibilityResolver is person's writeOnly field visible? (i.e. not writeOnly then)
     * @param int $evenId
     * @return array
     * @throws \Exception
     */
    public function createReferencedPerson(array $fieldsDefinition, int $acYear, string $searchType, bool $allowClear, IModifiabilityResolver $modifiabilityResolver, IVisibilityResolver $visibilityResolver, $evenId = 0) {
        $handler = $this->referencedPersonHandlerFactory->create($acYear, null, $evenId);

        $hiddenField = new ReferencedId($this->servicePerson, $handler, $this);

        $container = new ReferencedContainer($hiddenField);
        if ($searchType == self::SEARCH_NONE) {
            $container->setSearch();
        } else {
            $container->setSearch($this->createSearchControl($searchType), $this->createSearchCallback($searchType), $this->createTermToValuesCallback($searchType));
        }

        $container->setAllowClear($allowClear);
        $container->setOption('acYear', $acYear);
        $container->setOption('modifiabilityResolver', $modifiabilityResolver);
        $container->setOption('visibilityResolver', $visibilityResolver);
        foreach ($fieldsDefinition as $sub => $fields) {
            $subcontainer = new ContainerWithOptions();
            if ($sub == ReferencedPersonHandler::POST_CONTACT_DELIVERY) {
                $subcontainer->setOption('showGroup', true);
                $subcontainer->setOption('label', _('Doručovací adresa'));
            } elseif ($sub == ReferencedPersonHandler::POST_CONTACT_PERMANENT) {
                $subcontainer->setOption('showGroup', true);
                $label = _('Trvalá adresa');
                if (isset($container[ReferencedPersonHandler::POST_CONTACT_DELIVERY])) {
                    $label .= ' ' . _('(je-li odlišná od doručovací)');
                }
                $subcontainer->setOption('label', $label);
            }
            foreach ($fields as $fieldName => $metadata) {
                $control = $this->createField($sub, $fieldName, $acYear, $hiddenField, $metadata);
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
                                $hiddenField->setValue($foundPerson, IReferencedSetter::MODE_FORCE);
                                return false;
                            }
                            return true;
                        }, _('S e-mailem %value byla nalezena (formálně) jiná (ale pravděpodobně duplicitní) osoba, a tak ve formuláři nahradila původní.'));
                }

                $subcontainer->addComponent($control, $fieldName);
            }
            $container->addComponent($subcontainer, $sub);
        }

        return [
            $hiddenField,
            $container,
        ];
    }


    /**
     * @param ReferencedContainer $container
     * @param IModel|ModelPerson|null $model
     * @param string $mode
     * @return void
     */
    public function setModel(ReferencedContainer $container, IModel $model = null, string $mode = self::MODE_NORMAL): void {
        $acYear = $container->getOption('acYear');
        /** @var IModifiabilityResolver $modifiabilityResolver */
        $modifiabilityResolver = $container->getOption('modifiabilityResolver');
        /** @var IVisibilityResolver $visibilityResolver */
        $visibilityResolver = $container->getOption('visibilityResolver');
        $modifiable = $model ? $modifiabilityResolver->isModifiable($model) : true;
        $resolution = $model ? $modifiabilityResolver->getResolutionMode($model) : ReferencedPersonHandler::RESOLUTION_OVERWRITE;
        $visible = $model ? $visibilityResolver->isVisible($model) : true;
        $submittedBySearch = $container->isSearchSubmitted();
        $force = ($mode == self::MODE_FORCE);
        if ($mode == self::MODE_ROLLBACK) {
            $model = null;
        }

        $container->getReferencedId()->getHandler()->setResolution($resolution);
        $container->getComponent(ReferencedContainer::CONTROL_COMPACT)->setValue($model ? $model->getFullName() : null);
        foreach ($container->getComponents() as $sub => $subcontainer) {
            if (!$subcontainer instanceof Container) {
                continue;
            }
            /**
             * @var string $fieldName
             * @var BaseControl $component
             * TODO type safe
             */
            foreach ($subcontainer->getComponents() as $fieldName => $component) {
                if (isset($container[ReferencedPersonHandler::POST_CONTACT_DELIVERY])) {
                    $options = self::TARGET_FORM | self::HAS_DELIVERY;
                } else {
                    $options = self::TARGET_FORM;
                }
                $realValue = $this->getPersonValue($model, $sub, $fieldName, $acYear, $options); // not extrapolated
                $value = $this->getPersonValue($model, $sub, $fieldName, $acYear, $options | self::EXTRAPOLATE);
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
                if ($mode == self::MODE_ROLLBACK) {
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
     * @return AddressContainer|BaseControl
     * @throws \Exception
     */
    public function createField(string $sub, string $fieldName, int $acYear, HiddenField $hiddenField, array $metadata) {
        switch ($sub) {
            case  ReferencedPersonHandler::POST_CONTACT_DELIVERY:
            case ReferencedPersonHandler::POST_CONTACT_PERMANENT:
                if ($fieldName == 'address') {
                    $required = Arrays::get($metadata, 'required', false);
                    if ($required) {
                        $options = AddressFactory::REQUIRED;
                    } else {
                        $options = 0;
                    }
                    return $this->addressFactory->createAddress($options, $hiddenField);
                } else {
                    throw new InvalidArgumentException("Only 'address' field is supported.");
                }
            case 'person_has_flag':
                return $this->flagFactory->createFlag($hiddenField, $metadata);
            default:
                $control = null;
                switch ($sub) {
                    case 'person_info':
                        $control = $this->personInfoFactory->createField($fieldName);
                        break;
                    case 'person_history':
                        $control = $this->personHistoryFactory->createField($fieldName, $acYear);
                        break;
                    case 'person':
                        $control = $this->personFactory->createField($fieldName);
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
                        $conditioned = $control;
                        if ($hiddenField) {
                            $conditioned = $control->addConditionOn($hiddenField, Form::FILLED);
                        }
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
     * @param $component
     * @param $value
     * @return void
     */
    protected function setWriteOnly($component, $value) {
        if ($component instanceof IWriteOnly) {
            $component->setWriteOnly($value);
        } elseif ($component instanceof Container) {
            foreach ($component->getComponents() as $subcomponent) {
                $this->setWriteOnly($subcomponent, $value);
            }
        }
    }

    /**
     * @param $component
     * @return bool
     */
    protected function isWriteOnly($component) {
        if ($component instanceof IWriteOnly) {
            return true;
        } elseif ($component instanceof Container) {
            foreach ($component->getComponents() as $subcomponent) {
                if ($this->isWriteOnly($subcomponent)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param $searchType
     * @return AutocompleteSelectBox|TextInput
     */
    protected function createSearchControl($searchType) {

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
                $control = $this->personFactory->createPersonSelect(true, _('Jméno'), $this->personProvider);
                break;
            default:
                throw new InvalidArgumentException(_('Unknown search type'));
        }
        return $control;
    }

    protected function createSearchCallback(string $searchType): callable {
        $service = $this->servicePerson;
        switch ($searchType) {
            case self::SEARCH_EMAIL:
                return fn($term) => $service->findByEmail($term);
            case self::SEARCH_ID:
                return fn($term) => $service->findByPrimary($term);
            default:
                throw new InvalidArgumentException(_('Unknown search type'));
        }
    }

    protected function createTermToValuesCallback(string $searchType): callable {
        switch ($searchType) {
            case self::SEARCH_EMAIL:
                return fn($term) => ['person_info' => ['email' => $term]];
                break;
            case self::SEARCH_ID:
                return fn() => [];
            default:
                throw new InvalidArgumentException(_('Unknown search type'));
        }
    }

    /**
     * @param ModelPerson $person
     * @param $sub
     * @param $field
     * @param $acYear
     * @return bool
     */
    final public function isFilled(ModelPerson $person, $sub, $field, $acYear) {
        $value = $this->getPersonValue($person, $sub, $field, $acYear, self::TARGET_VALIDATION);
        return !($value === null || $value === '');
    }

    /**
     * @param ModelPerson|null $person
     * @param $sub
     * @param $field
     * @param $acYear
     * @param $options
     * @return bool|ModelPostContact|mixed|null
     */
    protected function getPersonValue(?ModelPerson $person, $sub, $field, $acYear, $options) {
        if (!$person) {
            return null;
        }
        switch ($sub) {
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
                break;
            case 'post_contact_p':
                if (($options & self::TARGET_VALIDATION) || !($options & self::HAS_DELIVERY)) {
                    return $person->getPermanentAddress();
                }
                return $person->getPermanentAddress(true);
                break;
            case 'person_has_flag':
                return ($flag = $person->getMPersonHasFlag($field)) ? (bool)$flag['value'] : null;
            default:
                throw new InvalidArgumentException("Unknown person sub '$sub'.");
        }
    }
}
