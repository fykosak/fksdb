<?php

namespace FKSDB\Components\Forms\Containers\Models;

use FKSDB\Components\DatabaseReflection\ColumnFactories\AbstractColumnException;
use FKSDB\Components\DatabaseReflection\OmittedControlException;
use FKSDB\Components\Forms\Containers\AddressContainer;
use FKSDB\Components\Forms\Containers\IWriteOnly;
use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\Components\Forms\Factories\AddressFactory;
use FKSDB\Components\Forms\Factories\FlagFactory;
use FKSDB\Components\Forms\Factories\PersonScheduleFactory;
use FKSDB\Components\Forms\Factories\ReferencedPerson\ReferencedPersonFactory;
use FKSDB\Components\Forms\Factories\SingleReflectionFormFactory;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Exceptions\NotImplementedException;
use FKSDB\ORM\IModel;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Models\ModelPostContact;
use FKSDB\ORM\Services\ServicePerson;
use Nette\Application\BadRequestException;
use Nette\ComponentModel\IComponent;
use Nette\ComponentModel\IContainer;
use Nette\DI\Container;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;
use Nette\InvalidArgumentException;
use Nette\InvalidStateException;
use Nette\Utils\JsonException;
use Persons\IModifiabilityResolver;
use Persons\IVisibilityResolver;
use Persons\ReferencedPersonHandler;

/**
 * Class ReferencedPersonContainer
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ReferencedPersonContainer extends ReferencedContainer {

    const TARGET_FORM = 0x1;
    const TARGET_VALIDATION = 0x2;
    const EXTRAPOLATE = 0x4;
    const HAS_DELIVERY = 0x8;

    /** @var IModifiabilityResolver */
    public $modifiabilityResolver;
    /** @var IVisibilityResolver */
    public $visibilityResolver;
    /** @var int */
    public $acYear;
    /** @var array */
    private $fieldsDefinition;
    /** @var ServicePerson */
    protected $servicePerson;
    /** @var SingleReflectionFormFactory */
    protected $singleReflectionFormFactory;
    /**@var FlagFactory */
    protected $flagFactory;
    /** @var AddressFactory */
    protected $addressFactory;
    /** @var PersonScheduleFactory */
    private $personScheduleFactory;
    /** @var ModelEvent */
    protected $event;
    /** @var bool */
    private $configured = false;

    /**
     * ReferencedPersonContainer constructor.
     * @param Container $container
     * @param IModifiabilityResolver $modifiabilityResolver
     * @param IVisibilityResolver $visibilityResolver
     * @param int $acYear
     * @param array $fieldsDefinition
     * @param ModelEvent|null $event
     * @param bool $allowClear
     */
    public function __construct(
        Container $container,
        IModifiabilityResolver $modifiabilityResolver,
        IVisibilityResolver $visibilityResolver,
        int $acYear,
        array $fieldsDefinition,
        $event,
        bool $allowClear
    ) {
        parent::__construct($container, $allowClear);
        $this->modifiabilityResolver = $modifiabilityResolver;
        $this->visibilityResolver = $visibilityResolver;
        $this->acYear = $acYear;
        $this->fieldsDefinition = $fieldsDefinition;
        $this->event = $event;
        $this->monitor(IContainer::class, function () {
            if (!$this->configured) {
                $this->configure();
            }
        });
    }

    /**
     * AbstractReferencedPersonFactory constructor.
     * @param AddressFactory $addressFactory
     * @param FlagFactory $flagFactory
     * @param ServicePerson $servicePerson
     * @param SingleReflectionFormFactory $singleReflectionFormFactory
     * @param PersonScheduleFactory $personScheduleFactory
     */
    public function injectPrimary(
        AddressFactory $addressFactory,
        FlagFactory $flagFactory,
        ServicePerson $servicePerson,
        SingleReflectionFormFactory $singleReflectionFormFactory,
        PersonScheduleFactory $personScheduleFactory
    ) {
        $this->servicePerson = $servicePerson;
        $this->singleReflectionFormFactory = $singleReflectionFormFactory;
        $this->flagFactory = $flagFactory;
        $this->addressFactory = $addressFactory;
        $this->personScheduleFactory = $personScheduleFactory;
    }

    /**
     * @return void
     * @throws AbstractColumnException
     * @throws BadRequestException
     * @throws BadTypeException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws OmittedControlException
     */
    protected function configure() {
        foreach ($this->fieldsDefinition as $sub => $fields) {
            $subContainer = new ContainerWithOptions();
            if ($sub == ReferencedPersonHandler::POST_CONTACT_DELIVERY) {
                $subContainer->setOption('showGroup', true);
                $subContainer->setOption('label', _('Doručovací adresa'));
            } elseif ($sub == ReferencedPersonHandler::POST_CONTACT_PERMANENT) {
                $subContainer->setOption('showGroup', true);
                $label = _('Trvalá adresa');
                if (isset($this[ReferencedPersonHandler::POST_CONTACT_DELIVERY])) {
                    $label .= ' ' . _('(je-li odlišná od doručovací)');
                }
                $subContainer->setOption('label', $label);
            }
            foreach ($fields as $fieldName => $metadata) {
                $control = $this->createField($sub, $fieldName, $metadata);
                $fullFieldName = "$sub.$fieldName";
                if ($this->getReferencedId()->getHandler()->isSecondaryKey($fullFieldName)) {
                    if ($fieldName != 'email') {
                        throw new InvalidStateException("Should define uniqueness validator for field $sub.$fieldName.");
                    }

                    $control->addCondition(function () { // we use this workaround not to call getValue inside validation out of transaction
                        $personId = $this->getReferencedId()->getValue(false);
                        return $personId && $personId != ReferencedId::VALUE_PROMISE;
                    })
                        ->addRule(function (BaseControl $control) use ($fullFieldName) {
                            $personId = $this->getReferencedId()->getValue(false);

                            $foundPerson = $this->getReferencedId()->getHandler()->findBySecondaryKey($fullFieldName, $control->getValue());
                            if ($foundPerson && $foundPerson->getPrimary() != $personId) {
                                $this->getReferencedId()->setValue($foundPerson, ReferencedId::MODE_FORCE);
                                return false;
                            }
                            return true;
                        }, _('S e-mailem %value byla nalezena (formálně) jiná (ale pravděpodobně duplicitní) osoba, a tak ve formuláři nahradila původní.'));
                }

                $subContainer->addComponent($control, $fieldName);
            }
            $this->addComponent($subContainer, $sub);
        }
    }

    /**
     * @param IModel|ModelPerson|null $model
     * @param string $mode
     * @return void
     * @throws JsonException
     */
    public function setModel(IModel $model = null, string $mode = ReferencedId::MODE_NORMAL) {

        $modifiable = $model ? $this->modifiabilityResolver->isModifiable($model) : true;
        $resolution = $model ? $this->modifiabilityResolver->getResolutionMode($model) : ReferencedPersonHandler::RESOLUTION_OVERWRITE;
        $visible = $model ? $this->visibilityResolver->isVisible($model) : true;
        $submittedBySearch = $this->getReferencedId()->getSearchContainer()->isSearchSubmitted();
        $force = ($mode === ReferencedId::MODE_FORCE);
        if ($mode === ReferencedId::MODE_ROLLBACK) {
            $model = null;
        }
        $this->getReferencedId()->getHandler()->setResolution($resolution);

        $this->getComponent(ReferencedContainer::CONTROL_COMPACT)->setValue($model ? $model->getFullName() : null);
        foreach ($this->getComponents() as $sub => $subContainer) {
            if (!$subContainer instanceof \Nette\Forms\Container) {
                continue;
            }
            /**
             * @var string $fieldName
             * @var BaseControl $component
             * TODO type safe
             */
            foreach ($subContainer->getComponents() as $fieldName => $component) {
                if (isset($this[ReferencedPersonHandler::POST_CONTACT_DELIVERY])) {
                    $options = self::TARGET_FORM | self::HAS_DELIVERY;
                } else {
                    $options = self::TARGET_FORM;
                }
                $realValue = $this->getPersonValue($model, $sub, $fieldName, $options); // not extrapolated
                $value = $this->getPersonValue($model, $sub, $fieldName, $options | self::EXTRAPOLATE);
                $controlModifiable = ($realValue !== null) ? $modifiable : true;
                $controlVisible = $this->isWriteOnly($component) ? $visible : true;

                if (!$controlVisible && !$controlModifiable) {
                    $this[$sub]->removeComponent($component);
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
     * @param array $metadata
     * @return IComponent|AddressContainer|BaseControl
     * @throws AbstractColumnException
     * @throws BadTypeException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws OmittedControlException
     * @throws BadRequestException
     */
    public function createField(string $sub, string $fieldName, array $metadata): IComponent {
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
                return $this->addressFactory->createAddress($options, $this->getReferencedId());
            } else {
                throw new InvalidArgumentException("Only 'address' field is supported.");
            }
        } elseif ($sub == 'person_has_flag') {
            return $this->flagFactory->createFlag($this->getReferencedId(), $metadata);
        } else {
            $control = null;
            switch ($sub) {
                case 'person_schedule':
                    $control = $this->personScheduleFactory->createField($fieldName, $this->event);
                    break;
                case 'person':
                case 'person_info':
                    $control = $this->singleReflectionFormFactory->createField($sub, $fieldName);
                    break;
                case 'person_history':
                    $control = $this->singleReflectionFormFactory->createField($sub, $fieldName, $this->acYear);
                    break;
                default:
                    throw new InvalidArgumentException();

            }
            $this->appendMetadata($control, $fieldName, $metadata);

            return $control;
        }
    }

    /**
     * @param BaseControl $control
     * @param string $fieldName
     * @param array $metadata
     * @return void
     */
    protected function appendMetadata(BaseControl $control, string $fieldName, array $metadata) {
        foreach ($metadata as $key => $value) {
            switch ($key) {
                case 'required':
                    if ($value) {
                        $conditioned = $control->addConditionOn($this->getReferencedId(), Form::FILLED);

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
        } elseif ($component instanceof IContainer) {
            foreach ($component->getComponents() as $subComponent) {
                $this->setWriteOnly($subComponent, $value);
            }
        }
    }

    protected function isWriteOnly(IComponent $component): bool {
        if ($component instanceof IWriteOnly) {
            return true;
        } elseif ($component instanceof IContainer) {
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
     * @return bool
     * @throws JsonException
     */
    final public function isFilled(ModelPerson $person, string $sub, string $field): bool {
        $value = $this->getPersonValue($person, $sub, $field, ReferencedPersonContainer::TARGET_VALIDATION);
        return !($value === null || $value === '');
    }

    /**
     * @param ModelPerson|null $person
     * @param string $sub
     * @param string $field
     * @param int $options
     * @return bool|ModelPostContact|mixed|null
     * @throws JsonException
     */
    protected function getPersonValue($person, string $sub, string $field, $options) {
        return ReferencedPersonFactory::getPersonValue($person, $sub, $field, $this->acYear, $options, $this->event);
    }
}
