<?php

namespace FKSDB\Components\Forms\Factories\ReferencedPerson;

use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Components\Forms\Containers\Models\IReferencedSetter;
use FKS\Components\Forms\Containers\IWriteonly;
use FKSDB\Components\Forms\Containers\Models\ReferencedContainer;
use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Forms\Factories\AddressFactory;
use FKSDB\Components\Forms\Factories\FlagFactory;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\Components\Forms\Factories\PersonHistoryFactory;
use FKSDB\Components\Forms\Factories\PersonInfoFactory;
use ModelPerson;
use Nette\Forms\Container;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\HiddenField;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\InvalidArgumentException;
use Nette\InvalidStateException;
use Nette\Object;
use Nette\Utils\Arrays;
use ORM\IModel;
use Persons\IModifialibityResolver;
use Persons\IVisibilityResolver;
use Persons\ReferencedPersonHandler;
use Persons\ReferencedPersonHandlerFactory;
use ServiceFlag;
use ServicePerson;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class AbstractReferencedPersonFactory extends Object implements IReferencedSetter {

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
     * @var PersonHistoryFactory
     */
    protected $personHistoryFactory;
    /**
     * @var PersonInfoFactory
     */
    protected $personInfoFactory;

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

    function __construct(AddressFactory $addressFactory, FlagFactory $flagFactory, ServicePerson $servicePerson, PersonFactory $personFactory, ReferencedPersonHandlerFactory $referencedPersonHandlerFactory, PersonProvider $personProvider, ServiceFlag $serviceFlag, PersonInfoFactory $personInfoFactory, PersonHistoryFactory $personHistoryFactory) {
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
     * @param integer $acYear
     * @param string $searchType
     * @param boolean $allowClear
     * @param IModifialibityResolver $modifiabilityResolver is person's filled field modifiable?
     * @param IVisibilityResolver $visibilityResolver is person's writeonly field visible? (i.e. not writeonly then)
     * @return array
     */
    public function createReferencedPerson($fieldsDefinition, $acYear, $searchType, $allowClear, IModifialibityResolver $modifiabilityResolver, IVisibilityResolver $visibilityResolver, $evenId = 0) {

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
            } else if ($sub == ReferencedPersonHandler::POST_CONTACT_PERMANENT) {
                $subcontainer->setOption('showGroup', true);
                $label = _('Trvalá adresa');
                if (isset($container[ReferencedPersonHandler::POST_CONTACT_DELIVERY])) {
                    $label .= ' ' . _('(je-li odlišná od doručovací)');
                }
                $subcontainer->setOption('label', $label);
            }

            foreach ($fields as $fieldName => $metadata) {
                if (is_scalar($metadata)) {
                    // old compatibility
                    throw new \InvalidArgumentException('Metadata must be a vector');
                }
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

        return array(
            $hiddenField,
            $container,
        );
    }


    public function setModel(ReferencedContainer $container, IModel $model = null, $mode = self::MODE_NORMAL, $metaData = []) {
        $acYear = $container->getOption('acYear');
        $modifiable = $model ? $container->getOption('modifiabilityResolver')->isModifiable($model) : true;
        $resolution = $model ? $container->getOption('modifiabilityResolver')->getResolutionMode($model) : ReferencedPersonHandler::RESOLUTION_OVERWRITE;
        $visible = $model ? $container->getOption('visibilityResolver')->isVisible($model) : true;
        $submittedBySearch = $container->isSearchSubmitted();
        $force = ($mode == self::MODE_FORCE);
        if ($mode == self::MODE_ROLLBACK) {
            $model = null;
        }

        $container->getReferencedId()->getHandler()->setResolution($resolution);
        $container->getComponent(ReferencedContainer::CONTROL_COMPACT)->setValue($model ? $model->getFullname() : null);

        foreach ($container->getComponents() as $sub => $subcontainer) {
            if (!$subcontainer instanceof Container) {
                continue;
            }

            foreach ($subcontainer->getComponents() as $fieldName => $component) {
                if (isset($container[ReferencedPersonHandler::POST_CONTACT_DELIVERY])) {
                    $options = self::TARGET_FORM | self::HAS_DELIVERY;
                } else {
                    $options = self::TARGET_FORM;
                }
                $realValue = $this->getPersonValue($model, $sub, $fieldName, $acYear, $options, $metaData); // not extrapolated
                $value = $this->getPersonValue($model, $sub, $fieldName, $acYear, $options | self::EXTRAPOLATE, $metaData);

                $controlModifiable = ($realValue !== null) ? $modifiable : true;
                $controlVisible = $this->isWriteonly($component) ? $visible : true;

                if (!$controlVisible && !$controlModifiable) {
                    $container[$sub]->removeComponent($component);
                } else if (!$controlVisible && $controlModifiable) {
                    $this->setWriteonly($component, true);
                    $component->setDisabled(false);
                } else if ($controlVisible && !$controlModifiable) {
                    $component->setDisabled();
                } else if ($controlVisible && $controlModifiable) {
                    $this->setWriteonly($component, false);
                    $component->setDisabled(false);
                }
                if ($mode == self::MODE_ROLLBACK) {
                    $component->setDisabled(false);
                    $this->setWriteonly($component, false);
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

    public function createField($sub, $fieldName, $acYear, HiddenField $hiddenField = null, $metadata = array()) {

        if (in_array($sub, array(
            ReferencedPersonHandler::POST_CONTACT_DELIVERY,
            ReferencedPersonHandler::POST_CONTACT_PERMANENT,
        ))) {
            if ($fieldName == 'address') {
                $required = Arrays::get($metadata, 'required', false);
                if ($required) {
                    $options = AddressFactory::REQUIRED;
                } else {
                    $options = 0;
                }
                $container = $this->addressFactory->createAddress($options, $hiddenField);
                return $container;
            } else {
                throw new InvalidArgumentException("Only 'address' field is supported.");
            }
        } else if ($sub == 'person_has_flag') {
            $control = $this->flagFactory->createFlag($hiddenField, $metadata);
            return $control;
        } else {
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

    protected function appendMetadata(BaseControl &$control, HiddenField $hiddenField, $fieldName, array $metadata) {
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

    protected function setWriteonly($component, $value) {
        if ($component instanceof IWriteonly) {
            $component->setWriteonly($value);
        } else if ($component instanceof Container) {
            foreach ($component->getComponents() as $subcomponent) {
                $this->setWriteonly($subcomponent, $value);
            }
        }
    }

    protected function isWriteonly($component) {
        if ($component instanceof IWriteonly) {
            return true;
        } else if ($component instanceof Container) {
            foreach ($component->getComponents() as $subcomponent) {
                if ($this->isWriteonly($subcomponent)) {
                    return true;
                }
            }
        }
        return false;
    }

    protected function createSearchControl($searchType) {
        switch ($searchType) {
            default:
            case self::SEARCH_EMAIL:
                $control = new TextInput(_('E-mail'));
                $control->addCondition(Form::FILLED)
                    ->addRule(Form::EMAIL, _('Neplatný tvar e-mailu.'));
                $control->setOption('description', _('Nejprve zkuste najít osobu v naší databázi podle e-mailu.'));
                break;
            case self::SEARCH_ID:
                $control = $this->personFactory->createPersonSelect(true, _('Jméno'), $this->personProvider);
        }
        return $control;
    }

    protected function createSearchCallback($searchType) {
        $service = $this->servicePerson;
        switch ($searchType) {
            default:
            case self::SEARCH_EMAIL:
                return function ($term) use ($service) {
                    return $service->findByEmail($term);
                };

                break;
            case self::SEARCH_ID:
                return function ($term) use ($service) {
                    return $service->findByPrimary($term);
                };
        }
    }

    protected function createTermToValuesCallback($searchType) {
        switch ($searchType) {
            default:
            case self::SEARCH_EMAIL:
                return function ($term) {
                    return ['person_info' => ['email' => $term]];
                };
                break;
            case self::SEARCH_ID:
                return function () {
                    return array();
                };
        }
    }

    public final function isFilled(ModelPerson $person, $sub, $field, $acYear, $metaData) {
        $value = $this->getPersonValue($person, $sub, $field, $acYear, self::TARGET_VALIDATION, $metaData);
        return !($value === null || $value === '');
    }

    protected function getPersonValue(ModelPerson $person = null, $sub, $field, $acYear, $options, $metaData) {
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

