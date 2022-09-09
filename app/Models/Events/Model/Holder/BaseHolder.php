<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Model\Holder;

use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Models\Events\FormAdjustments\FormAdjustment;
use FKSDB\Models\Events\Machine\BaseMachine;
use FKSDB\Models\Events\Machine\Transition;
use FKSDB\Models\Events\Processing\GenKillProcessing;
use FKSDB\Models\Events\Processing\Processing;
use FKSDB\Models\Expressions\NeonSchemaException;
use FKSDB\Models\Expressions\NeonScheme;
use FKSDB\Models\Events\Model\ExpressionEvaluator;
use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\EventParticipantStatus;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Machine\AbstractMachine;
use Fykosak\NetteORM\Model;
use Fykosak\NetteORM\Service;
use FKSDB\Models\ORM\ModelsMulti\Events\ModelMDsefParticipant;
use FKSDB\Models\ORM\ServicesMulti\ServiceMulti;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\Utils\Logging\Logger;
use Nette\Forms\Form;
use Nette\InvalidArgumentException;
use Nette\Neon\Neon;
use Nette\Utils\ArrayHash;

class BaseHolder implements ModelHolder
{
    public string $name;
    public ?string $description;
    public string $label;

    /** @var bool|callable */
    private $modifiable;
    /** @var bool|callable */
    private $visible;

    private ExpressionEvaluator $evaluator;
    public DataValidator $validator;
    public EventModel $event;
    /** @var Service|ServiceMulti */
    public $service;
    /** @var EventParticipantModel|ModelMDsefParticipant|null */
    private ?Model $model;
    public array $data = [];

    public array $paramScheme;
    private array $parameters;

    /** @var Field[] */
    private array $fields = [];
    /** @var FormAdjustment[] */
    private array $formAdjustments = [];
    /** @var Processing[] */
    private array $processings = [];

    public function __construct(string $name)
    {
        /*
         * This implicit processing is the first. It's not optimal
         * and it may be subject to change.
         */
        $this->processings[] = new GenKillProcessing();
        $this->name = $name;
    }

    public function addFormAdjustment(FormAdjustment $formAdjustment): void
    {
        $this->formAdjustments[] = $formAdjustment;
    }

    public function addProcessing(Processing $processing): void
    {
        $this->processings[] = $processing;
    }

    /**
     * Apply processings to the values and sets them to the ORM model.
     */
    public function processFormValues(
        ArrayHash $values,
        BaseMachine $machine,
        ?Transition $transition,
        Logger $logger,
        ?Form $form
    ): ?EventParticipantStatus {
        $newState = null;
        if ($transition) {
            $newState = $transition->targetStateEnum;
        }
        foreach ($this->processings as $processing) {
            $result = $processing->process($newState, $values, $machine, $this, $logger, $form);
            if ($result) {
                $newState = $result;
            }
        }

        return $newState;
    }

    public function adjustForm(Form $form): void
    {
        foreach ($this->formAdjustments as $adjustment) {
            $adjustment->adjust($form, $this);
        }
    }

    public function addField(Field $field): void
    {
        $field->baseHolder = $this;
        $this->fields[$field->name] = $field;
    }

    /**
     * @return Field[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @param bool|callable $modifiable
     */
    public function setModifiable($modifiable): void
    {
        $this->modifiable = $modifiable;
    }

    /**
     * @param bool|callable $visible
     */
    public function setVisible($visible): void
    {
        $this->visible = $visible;
    }

    /**
     * @throws NeonSchemaException
     */
    private function setEvent(EventModel $event): void
    {
        $this->event = $event;
        $this->data['event_id'] = $this->event->getPrimary();
        $this->cacheParameters();
    }

    /**
     * @throws NeonSchemaException
     */
    public function inferEvent(EventModel $event): void
    {
        $this->setEvent($event);
    }

    public function setParamScheme(array $paramScheme): void
    {
        $this->paramScheme = $paramScheme;
    }

    public function setEvaluator(ExpressionEvaluator $evaluator): void
    {
        $this->evaluator = $evaluator;
    }

    public function setValidator(DataValidator $validator): void
    {
        $this->validator = $validator;
    }

    public function isVisible(): bool
    {
        return $this->evaluator->evaluate($this->visible, $this);
    }

    public function isModifiable(): bool
    {
        return $this->evaluator->evaluate($this->modifiable, $this);
    }

    /**
     * @return ModelMDsefParticipant|EventParticipantModel
     */
    public function getModel(): ?Model
    {
        return $this->model ?? null;
    }

    public function setModel(?Model $model): void
    {
        $this->model = $model;
    }

    public function saveModel(): void
    {
        if ($this->getModelState() == AbstractMachine::STATE_TERMINATED) {
            $model = $this->getModel();
            if ($model) {
                $this->service->disposeModel($model);
            }
        } elseif ($this->getModelState() != AbstractMachine::STATE_INIT) {
            $this->model = $this->service->storeModel($this->data, $this->getModel());
        }
    }

    public function getModelState(): EventParticipantStatus
    {
        $model = $this->getModel();
        if (isset($this->data['status'])) {
            return EventParticipantStatus::tryFrom($this->data['status']);
        }
        if ($model) {
            return $model->status;
        }

        return EventParticipantStatus::tryFrom(AbstractMachine::STATE_INIT);
    }

    public function setModelState(EventParticipantStatus $state): void
    {
        $this->data['status'] = $state->value;
    }

    /**
     * @param Service|ServiceMulti $service
     */
    public function setService($service): void
    {
        $this->service = $service;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public static function getBareColumn(string $column): ?string
    {
        $column = str_replace(':', '.', $column);
        $pos = strrpos($column, '.');
        return $pos === false ? $column : substr($column, $pos + 1);
    }

    /**
     * @return Field[]
     */
    public function getDeterminingFields(): array
    {
        return array_filter($this->fields, fn(Field $field): bool => $field->determining);
    }

    public function createFormContainer(): ContainerWithOptions
    {
        $container = new ContainerWithOptions();
        $container->setOption('label', $this->label);
        $container->setOption('description', $this->description);

        foreach ($this->fields as $name => $field) {
            if (!$field->isVisible()) {
                continue;
            }
            $component = $field->createFormComponent();
            $container->addComponent($component, $name);
            $field->setFieldDefaultValue($component);
        }
        return $container;
    }

    /**
     * @throws \ReflectionException
     */
    public function getPerson(): ?PersonModel
    {
        /** @var PersonModel $model */
        try {
            $app = $this->getModel();
            if (!$app) {
                return null;
            }
            return $app->getReferencedModel(PersonModel::class);
        } catch (CannotAccessModelException $exception) {
            return null;
        }
    }

    public function __toString(): string
    {
        return $this->name;
    }

    /*
     * Parameter handling
     */
    /**
     * @throws NeonSchemaException
     */
    private function cacheParameters(): void
    {
        $parameters = $this->event->parameters ?? '';
        $parameters = $parameters ? Neon::decode($parameters) : [];
        if (is_string($parameters)) {
            throw new NeonSchemaException('Parameters must be an array string given');
        }
        $this->parameters = NeonScheme::readSection($parameters, $this->paramScheme);
    }

    /**
     * @return mixed
     */
    public function getParameter(string $name)
    {
        try {
            return $this->parameters[$name] ?? null;
        } catch (InvalidArgumentException $exception) {
            throw new InvalidArgumentException(
                "No parameter '$name' for event " . $this->event . '.',
                null,
                $exception
            );
        }
    }

    public function updateState(EnumColumn $newState): void
    {
        $this->service->storeModel(['status' => $newState->value], $this->model);
    }

    public function getState(): EnumColumn
    {
        return $this->model->status;
    }
}
