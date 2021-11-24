<?php

namespace FKSDB\Components\Forms\Containers\Models;

use FKSDB\Components\Forms\Controls\WriteOnly\WriteOnly;
use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\Components\Forms\Factories\AddressFactory;
use FKSDB\Components\Forms\Factories\FlagFactory;
use FKSDB\Components\Forms\Factories\PersonScheduleFactory;
use FKSDB\Components\Forms\Factories\ReferencedPerson\ReferencedPersonFactory;
use FKSDB\Components\Forms\Factories\SingleReflectionFormFactory;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\ModelContestYear;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\ORM\OmittedControlException;
use FKSDB\Models\ORM\Services\ServicePerson;
use FKSDB\Models\Persons\ModifiabilityResolver;
use FKSDB\Models\Persons\VisibilityResolver;
use FKSDB\Models\Persons\ReferencedPersonHandler;
use Nette\Application\BadRequestException;
use Nette\ComponentModel\IComponent;
use Nette\ComponentModel\IContainer;
use Nette\Database\Table\ActiveRow;
use Nette\DI\Container;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;
use Nette\InvalidArgumentException;
use Nette\InvalidStateException;
use Tracy\Debugger;

class ReferencedPersonContainer extends ReferencedContainer
{

    public ModifiabilityResolver $modifiabilityResolver;

    public VisibilityResolver $visibilityResolver;

    public ModelContestYear $contestYear;

    private array $fieldsDefinition;

    protected ServicePerson $servicePerson;

    protected SingleReflectionFormFactory $singleReflectionFormFactory;

    protected FlagFactory $flagFactory;

    protected AddressFactory $addressFactory;

    private PersonScheduleFactory $personScheduleFactory;

    protected ?ModelEvent $event;

    private bool $configured = false;

    public function __construct(
        Container $container,
        ModifiabilityResolver $modifiabilityResolver,
        VisibilityResolver $visibilityResolver,
        ModelContestYear $contestYear,
        array $fieldsDefinition,
        ?ModelEvent $event,
        bool $allowClear
    ) {
        parent::__construct($container, $allowClear);
        $this->modifiabilityResolver = $modifiabilityResolver;
        $this->visibilityResolver = $visibilityResolver;
        $this->contestYear = $contestYear;
        $this->fieldsDefinition = $fieldsDefinition;
        $this->event = $event;
        $this->monitor(IContainer::class, function (): void {
            if (!$this->configured) {
                $this->configure();
            }
        });
    }

    final public function injectPrimary(
        AddressFactory $addressFactory,
        FlagFactory $flagFactory,
        ServicePerson $servicePerson,
        SingleReflectionFormFactory $singleReflectionFormFactory,
        PersonScheduleFactory $personScheduleFactory
    ): void {
        $this->servicePerson = $servicePerson;
        $this->singleReflectionFormFactory = $singleReflectionFormFactory;
        $this->flagFactory = $flagFactory;
        $this->addressFactory = $addressFactory;
        $this->personScheduleFactory = $personScheduleFactory;
    }

    /**
     * @throws BadRequestException
     * @throws BadTypeException
     * @throws NotImplementedException
     * @throws OmittedControlException
     */
    protected function configure(): void
    {
        foreach ($this->fieldsDefinition as $sub => $fields) {
            $subContainer = new ContainerWithOptions();
            if ($sub == ReferencedPersonHandler::POST_CONTACT_DELIVERY) {
                $subContainer->setOption('showGroup', true);
                $subContainer->setOption('label', _('Deliver address'));
            } elseif ($sub == ReferencedPersonHandler::POST_CONTACT_PERMANENT) {
                $subContainer->setOption('showGroup', true);
                $label = _('Permanent address');
                if (isset($this[ReferencedPersonHandler::POST_CONTACT_DELIVERY])) {
                    $label .= ' ' . _('(when different from delivery address)');
                }
                $subContainer->setOption('label', $label);
            }
            foreach ($fields as $fieldName => $metadata) {
                $control = $this->createField($sub, $fieldName, $metadata);
                $fullFieldName = "$sub.$fieldName";
                if ($this->getReferencedId()->getHandler()->isSecondaryKey($fullFieldName)) {
                    if ($fieldName != 'email') {
                        throw new InvalidStateException(
                            "Should define uniqueness validator for field $sub.$fieldName."
                        );
                    }

                    $control->addCondition(
                        function (
                        ): bool { // we use this workaround not to call getValue inside validation out of transaction
                            $personId = $this->getReferencedId()->getValue(false);
                            return $personId && $personId != ReferencedId::VALUE_PROMISE;
                        }
                    )
                        ->addRule(
                            function (BaseControl $control) use ($fullFieldName): bool {
                                $personId = $this->getReferencedId()->getValue(false);

                                $foundPerson = $this->getReferencedId()->getHandler()->findBySecondaryKey(
                                    $fullFieldName,
                                    $control->getValue()
                                );
                                if ($foundPerson && $foundPerson->getPrimary() != $personId) {
                                    $this->getReferencedId()->setValue($foundPerson, ReferencedId::MODE_FORCE);
                                    return false;
                                }
                                return true;
                            },
                            _(
                                'There is (formally) different person with email %value. Probably it is a duplicate so it substituted original data in the form.'
                            )
                        );
                }

                $subContainer->addComponent($control, $fieldName);
            }
            $this->addComponent($subContainer, $sub);
        }
    }

    /**
     * @param ActiveRow|ModelPerson|null $model
     */
    public function setModel(?ActiveRow $model, string $mode): void
    {
        $resolution = $this->modifiabilityResolver->getResolutionMode($model);
        $modifiable = $this->modifiabilityResolver->isModifiable($model);
        $visible = $this->visibilityResolver->isVisible($model);
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
                $realValue = $this->getPersonValue(
                    $model,
                    $sub,
                    $fieldName,
                    false,
                    isset($this[ReferencedPersonHandler::POST_CONTACT_DELIVERY])
                ); // not extrapolated
                $value = $this->getPersonValue(
                    $model,
                    $sub,
                    $fieldName,
                    true,
                    isset($this[ReferencedPersonHandler::POST_CONTACT_DELIVERY])
                );
                $controlModifiable = ($realValue !== null) ? $modifiable : true;
                $controlVisible = $this->isWriteOnly($component) ? $visible : true;
                if (!$controlVisible && !$controlModifiable) {
                    $this[$sub]->removeComponent($component);
                } elseif (!$controlVisible && $controlModifiable) {
                    $this->setWriteOnly($component, true);
                    $component->setDisabled(false);
                } elseif ($controlVisible && !$controlModifiable) {
                    $component->setDisabled();
                    // $component->setOmitted(false);
                    $component->setValue($value);
                    // $component->setDefaultValue($value);
                } elseif ($controlVisible && $controlModifiable) {
                    $this->setWriteOnly($component, false);
                    $component->setDisabled(false);
                }
                if ($mode == ReferencedId::MODE_ROLLBACK) {
                    $component->setDisabled(false);
                    $this->setWriteOnly($component, false);
                } else {
                    if (
                        $this->getReferencedId()->getSearchContainer()->isSearchSubmitted()
                        || ($mode === ReferencedId::MODE_FORCE)
                    ) {
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
     * @return IComponent|BaseControl
     * @throws BadTypeException
     * @throws NotImplementedException
     * @throws OmittedControlException
     * @throws BadRequestException
     */
    public function createField(string $sub, string $fieldName, array $metadata): IComponent
    {
        switch ($sub) {
            case ReferencedPersonHandler::POST_CONTACT_DELIVERY:
            case ReferencedPersonHandler::POST_CONTACT_PERMANENT:
                if ($fieldName == 'address') {
                    return $this->addressFactory->createAddress(
                        $this->getReferencedId(),
                        (bool)$metadata['required'] ?? false
                    );
                } else {
                    throw new InvalidArgumentException("Only 'address' field is supported.");
                }
            case 'person_has_flag':
                return $this->flagFactory->createFlag($this->getReferencedId(), $metadata);
            case 'person_schedule':
                $control = $this->personScheduleFactory->createField(
                    $fieldName,
                    $this->event,
                    $metadata['label'] ?? null
                );
                break;
            case 'person':
            case 'person_info':
                $control = $this->singleReflectionFormFactory->createField($sub, $fieldName);
                break;
            case 'person_history':
                $control = $this->singleReflectionFormFactory->createField($sub, $fieldName, $this->contestYear);
                break;
            default:
                throw new InvalidArgumentException();
        }
        $this->appendMetadata($control, $fieldName, $metadata);

        return $control;
    }

    protected function appendMetadata(BaseControl $control, string $fieldName, array $metadata): void
    {
        foreach ($metadata as $key => $value) {
            switch ($key) {
                case 'required':
                    if ($value) {
                        $conditioned = $control->addConditionOn($this->getReferencedId(), Form::FILLED);

                        if ($fieldName == 'agreed') { // NOTE: this may need refactoring when more customization requirements occurre
                            $conditioned->addRule(Form::FILLED, _('Confirmation is necessary to proceed.'));
                        } else {
                            $conditioned->addRule(Form::FILLED, _('Field %label is required.'));
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

    protected function setWriteOnly(IComponent $component, bool $value): void
    {
        if ($component instanceof WriteOnly) {
            $component->setWriteOnly($value);
        } elseif ($component instanceof IContainer) {
            foreach ($component->getComponents() as $subComponent) {
                $this->setWriteOnly($subComponent, $value);
            }
        }
    }

    protected function isWriteOnly(IComponent $component): bool
    {
        if ($component instanceof WriteOnly) {
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
     * @return mixed
     */
    protected function getPersonValue(
        ?ModelPerson $person,
        string $sub,
        string $field,
        bool $extrapolate = false,
        bool $hasDelivery = false,
        bool $targetValidation = false
    ) {
        return ReferencedPersonFactory::getPersonValue(
            $person,
            $sub,
            $field,
            $this->contestYear,
            $extrapolate,
            $hasDelivery,
            $targetValidation,
            $this->event
        );
    }
}
