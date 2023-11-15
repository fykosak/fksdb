<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Containers\Models;

use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Controls\ReferencedIdMode;
use FKSDB\Components\Forms\Controls\WriteOnly\WriteOnly;
use FKSDB\Components\Forms\Factories\FlagFactory;
use FKSDB\Components\Forms\Factories\SingleReflectionFormFactory;
use FKSDB\Components\Forms\Referenced\Address\AddressDataContainer;
use FKSDB\Components\Schedule\Input\ScheduleContainer;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Columns\OmittedControlException;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\PersonService;
use FKSDB\Models\Persons\ReferencedPersonHandler;
use FKSDB\Models\Persons\ResolutionMode;
use FKSDB\Models\Persons\Resolvers\Resolver;
use Fykosak\NetteORM\Model\Model;
use Nette\Application\BadRequestException;
use Nette\ComponentModel\IComponent;
use Nette\DI\Container;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;
use Nette\InvalidArgumentException;

/**
 * @phpstan-extends ReferencedContainer<PersonModel>
 * @phpstan-type EvaluatedFieldMetaData array{required?:bool,caption?:string|null,description?:string|null}
 * @phpstan-type EvaluatedFieldsDefinition array<string,array<string,EvaluatedFieldMetaData>>
 */
class ReferencedPersonContainer extends ReferencedContainer
{
    public Resolver $resolver;
    public ?ContestYearModel $contestYear;
    /** @phpstan-var EvaluatedFieldsDefinition */
    private array $fieldsDefinition;
    protected PersonService $personService;
    protected SingleReflectionFormFactory $singleReflectionFormFactory;
    protected FlagFactory $flagFactory;
    protected ?EventModel $event;

    /**
     * @phpstan-param EvaluatedFieldsDefinition $fieldsDefinition
     */
    public function __construct(
        Container $container,
        Resolver $resolver,
        ?ContestYearModel $contestYear,
        array $fieldsDefinition,
        ?EventModel $event,
        bool $allowClear
    ) {
        parent::__construct($container, $allowClear);
        $this->resolver = $resolver;
        $this->contestYear = $contestYear;
        $this->fieldsDefinition = $fieldsDefinition;
        $this->event = $event;
    }

    final public function injectPrimary(
        FlagFactory $flagFactory,
        PersonService $personService,
        SingleReflectionFormFactory $singleReflectionFormFactory
    ): void {
        $this->personService = $personService;
        $this->singleReflectionFormFactory = $singleReflectionFormFactory;
        $this->flagFactory = $flagFactory;
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
            $subContainer = new ContainerWithOptions($this->container);
            if ($sub == ReferencedPersonHandler::POST_CONTACT_DELIVERY) {
                $subContainer->setOption('label', _('Deliver address'));
            } elseif ($sub == ReferencedPersonHandler::POST_CONTACT_PERMANENT) {
                $label = _('Permanent address');
                if ($this->getComponent(ReferencedPersonHandler::POST_CONTACT_DELIVERY, false)) {
                    $label .= ' ' . _('(when different from delivery address)');
                }
                $subContainer->setOption('label', $label);
            }
            if (
                $sub == ReferencedPersonHandler::POST_CONTACT_DELIVERY ||
                $sub == ReferencedPersonHandler::POST_CONTACT_PERMANENT
            ) {
                if (isset($fields['address'])) {
                    $control = new AddressDataContainer(
                        $this->container,
                        true,
                        (bool)($fields['address']['required'] ?? false)
                    );
                } else {
                    $control = new AddressDataContainer($this->container, true, (bool)($fields['required'] ?? false));
                }
                $subContainer->setOption('showGroup', true);
                $subContainer->addComponent($control, 'address');
            } else {
                foreach ($fields as $fieldName => $metadata) {
                    $control = $this->createField($sub, $fieldName, $metadata);
                    $subContainer->addComponent($control, $fieldName);
                }
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

        /** @phpstan-ignore-next-line */
        $this->getComponent(ReferencedContainer::CONTROL_COMPACT)->setValue($model ? $model->getFullName() : null);
        /**
         * @var string $sub
         */
        foreach ($this->getComponents() as $sub => $subContainer) {
            if (!$subContainer instanceof ContainerWithOptions) {
                continue;
            }
            /**
             * @var BaseControl|ModelContainer|AddressDataContainer|ScheduleContainer $component
             * @var string $fieldName
             */
            foreach ($subContainer->getComponents() as $fieldName => $component) {
                $value = ReferencedPersonHandler::getPersonValue(
                    $model,
                    $sub,
                    $fieldName,
                    $this->contestYear,
                    $this->event
                );
                $controlModifiable = isset($value) ? $modifiable : true;
                $controlVisible = $this->isWriteOnly($component) ? $visible : true;
                if (!$controlVisible && !$controlModifiable) {
                    /** @phpstan-ignore-next-line */
                    $this[$sub]->removeComponent($component);
                    /** @phpstan-ignore-next-line */
                } elseif (!$controlVisible && $controlModifiable) {
                    $this->setWriteOnly($component, true);
                    $component->setDisabled(false);
                } elseif ($controlVisible && !$controlModifiable) {
                    $component->setHtmlAttribute('readonly', 'readonly');
                    if ($component instanceof ContainerWithOptions) {
                        $component->setValues($value);
                    } else {
                        $component->setValue($value);
                    }
                } elseif ($controlVisible && $controlModifiable) {
                    $this->setWriteOnly($component, false);
                    $component->setDisabled(false);
                }
                if ($mode->value == ReferencedIdMode::ROLLBACK) {
                    $component->setDisabled(false);
                    $this->setWriteOnly($component, false);
                } else {
                    if ($component instanceof AddressDataContainer) {
                        $component->setModel($value ? $value->address : null, $mode);
                    } elseif ($component instanceof ScheduleContainer) {
                        $component->setValues($value);
                    } elseif (
                        $this->getReferencedId()->searchContainer->isSearchSubmitted()
                        || ($mode->value === ReferencedIdMode::FORCE)
                    ) {
                        $component->setValue($value); //@phpstan-ignore-line
                    } else {
                        $component->setDefaultValue($value); //@phpstan-ignore-line
                    }
                    if ($value && $resolution->value == ResolutionMode::EXCEPTION) {
                        $component->setHtmlAttribute('readonly', 'readonly');
                        // $component->setDisabled(); // could not store different value anyway
                    }
                }
            }
        }
    }

    /**
     * @return ContainerWithOptions|BaseControl|AddressDataContainer
     * @throws BadTypeException
     * @throws NotImplementedException
     * @throws OmittedControlException
     * @throws BadRequestException
     * @phpstan-param EvaluatedFieldMetaData $metadata
     */
    public function createField(string $sub, string $fieldName, array $metadata): IComponent
    {
        switch ($sub) {
            case 'person_has_flag':
                return $this->flagFactory->createFlag($this->getReferencedId(), $metadata);
            case 'person_schedule':
                return new ScheduleContainer(
                    $this->container,
                    $this->event,
                    $metadata
                );
            case 'person':
            case 'person_info':
                $control = $this->singleReflectionFormFactory->createField($sub, $fieldName);
                break;
            case 'person_history':
                if (!isset($this->contestYear)) {
                    throw new \InvalidArgumentException('Cannot get person_history without ContestYear');
                }
                if ($fieldName === 'study_year_new') {
                    $control = $this->singleReflectionFormFactory->createField(
                        $sub,
                        $fieldName,
                        $this->contestYear,
                        $metadata['flag'] //@phpstan-ignore-line
                    );
                } else {
                    $control = $this->singleReflectionFormFactory->createField($sub, $fieldName, $this->contestYear);
                }
                break;
            default:
                throw new InvalidArgumentException();
        }
        $this->appendMetadataField($control, $fieldName, $metadata);
        return $control;
    }

    /**
     * @phpstan-param array{required?:bool,caption?:string|null,description?:string|null} $metadata
     */
    protected function appendMetadataField(BaseControl $control, string $fieldName, array $metadata): void
    {
        foreach ($metadata as $key => $value) {
            switch ($key) {
                case 'required':
                    if ($value) {
                        $conditioned = $control->addConditionOn($this->getReferencedId(), Form::FILLED);

                        if ($fieldName == 'agreed') {
                            // NOTE: this may need refactoring when more customization requirements occurre
                            $conditioned->addRule(Form::FILLED, _('Confirmation is necessary to proceed.'));
                            break;
                        }
                        $conditioned->addRule(Form::FILLED, _('Field %label is required.'));
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
        } elseif ($component instanceof ContainerWithOptions) {
            foreach ($component->getComponents() as $subComponent) {
                $this->setWriteOnly($subComponent, $value);
            }
        }
    }

    protected function isWriteOnly(IComponent $component): bool
    {
        if ($component instanceof WriteOnly) {
            return true;
        } elseif ($component instanceof ContainerWithOptions) {
            foreach ($component->getComponents() as $subComponent) {
                if ($this->isWriteOnly($subComponent)) {
                    return true;
                }
            }
        }
        return false;
    }
}
