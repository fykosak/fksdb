<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Containers\Models;

use FKSDB\Components\Forms\Controls\ReferencedIdMode;
use FKSDB\Components\Forms\Controls\WriteOnly\WriteOnly;
use FKSDB\Components\Forms\Factories\AddressFactory;
use FKSDB\Components\Forms\Factories\FlagFactory;
use FKSDB\Components\Forms\Factories\PersonScheduleFactory;
use FKSDB\Components\Forms\Factories\ReferencedPerson\ReferencedPersonFactory;
use FKSDB\Components\Forms\Factories\SingleReflectionFormFactory;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\OmittedControlException;
use FKSDB\Models\ORM\Services\PersonService;
use FKSDB\Models\Persons\ResolutionMode;
use FKSDB\Models\Persons\Resolvers\Resolver;
use FKSDB\Models\Persons\ReferencedPersonHandler;
use Fykosak\NetteORM\Model;
use Nette\Application\BadRequestException;
use Nette\ComponentModel\IComponent;
use Nette\ComponentModel\IContainer;
use Nette\DI\Container;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;
use Nette\InvalidArgumentException;

class ReferencedPersonContainer extends ReferencedContainer
{

    public Resolver $resolver;
    public ContestYearModel $contestYear;
    private array $fieldsDefinition;
    protected PersonService $personService;
    protected SingleReflectionFormFactory $singleReflectionFormFactory;
    protected FlagFactory $flagFactory;
    protected AddressFactory $addressFactory;
    private PersonScheduleFactory $personScheduleFactory;
    protected ?EventModel $event;

    private bool $configured = false;

    public function __construct(
        Container $container,
        Resolver $resolver,
        ContestYearModel $contestYear,
        array $fieldsDefinition,
        ?EventModel $event,
        bool $allowClear
    ) {
        parent::__construct($container, $allowClear);
        $this->resolver = $resolver;
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
        PersonService $personService,
        SingleReflectionFormFactory $singleReflectionFormFactory,
        PersonScheduleFactory $personScheduleFactory
    ): void {
        $this->personService = $personService;
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
                if ($this->getComponent(ReferencedPersonHandler::POST_CONTACT_DELIVERY, false)) {
                    $label .= ' ' . _('(when different from delivery address)');
                }
                $subContainer->setOption('label', $label);
            }
            foreach ($fields as $fieldName => $metadata) {
                $control = $this->createField($sub, $fieldName, $metadata);
                $subContainer->addComponent($control, $fieldName);
            }
            $this->addComponent($subContainer, $sub);
        }
    }

    /**
     * @param PersonModel|null $model
     */
    public function setModel(?Model $model, ReferencedIdMode $mode): void
    {
        $resolution = $this->resolver->getResolutionMode($model);
        $modifiable = $this->resolver->isModifiable($model);
        $visible = $this->resolver->isVisible($model);
        if ($mode->value === ReferencedIdMode::ROLLBACK) {
            $model = null;
        }
        $this->getReferencedId()->handler->setResolution($resolution);

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
                $controlModifiable = isset($realValue) ? $modifiable : true;
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
                if ($mode->value == ReferencedIdMode::ROLLBACK) {
                    $component->setDisabled(false);
                    $this->setWriteOnly($component, false);
                } else {
                    if (
                        $this->getReferencedId()->searchContainer->isSearchSubmitted()
                        || ($mode->value === ReferencedIdMode::FORCE)
                    ) {
                        $component->setValue($value);
                    } else {
                        $component->setDefaultValue($value);
                    }
                    if ($realValue && $resolution->value == ResolutionMode::EXCEPTION) {
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

                        if ($fieldName == 'agreed') {
                            // NOTE: this may need refactoring when more customization requirements occurre
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
        ?PersonModel $person,
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
