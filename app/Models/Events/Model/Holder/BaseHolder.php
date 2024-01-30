<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Model\Holder;

use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Models\Events\FormAdjustments\FormAdjustment;
use FKSDB\Models\Events\Processing\Processing;
use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\EventParticipantStatus;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\EventParticipantService;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Machine\Machine;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Nette\DI\Container;

/**
 * @phpstan-implements ModelHolder<EventParticipantStatus,EventParticipantModel>
 */
class BaseHolder implements ModelHolder
{
    /** @phpstan-var bool|(callable(BaseHolder):bool)|null */
    private $modifiable;
    public EventModel $event;
    private EventParticipantService $service;
    private ?EventParticipantModel $model;
    /** @phpstan-var array<string,mixed> */
    public array $data = [];

    /** @phpstan-var Field[] */
    private array $fields = [];
    /** @phpstan-var FormAdjustment<BaseHolder>[] */
    public array $formAdjustments = [];
    /** @phpstan-var Processing[] */
    public array $processings = [];

    public function __construct(EventParticipantService $service)
    {
        $this->service = $service;
    }

    /**
     * @phpstan-param FormAdjustment<BaseHolder> $formAdjustment
     */
    public function addFormAdjustment(FormAdjustment $formAdjustment): void
    {
        $this->formAdjustments[] = $formAdjustment;
    }

    public function addProcessing(Processing $processing): void
    {
        $this->processings[] = $processing;
    }

    public function addField(Field $field): void
    {
        $this->fields[$field->name] = $field;
    }

    /**
     * @phpstan-return Field[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @phpstan-param bool|callable(BaseHolder):bool $modifiable
     */
    public function setModifiable($modifiable): void
    {
        $this->modifiable = $modifiable;
    }

    public function setEvent(EventModel $event): void
    {
        $this->event = $event;
        $this->data['event_id'] = $this->event->getPrimary();
    }

    public function isModifiable(): bool
    {
        if (is_callable($this->modifiable)) {
            return ($this->modifiable)($this);
        }
        return (bool)$this->modifiable;
    }

    public function getModel(): ?EventParticipantModel
    {
        return $this->model ?? null;
    }

    public function setModel(?EventParticipantModel $model): void
    {
        $this->model = $model;
    }

    public function saveModel(): void
    {
        if ($this->getModelState() != Machine::STATE_INIT) {
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

        return EventParticipantStatus::from(Machine::STATE_INIT);
    }

    public function setModelState(EventParticipantStatus $state): void
    {
        $this->data['status'] = $state->value;
    }

    public static function getBareColumn(string $column): ?string
    {
        $column = str_replace(':', '.', $column);
        $pos = strrpos($column, '.');
        return $pos === false ? $column : substr($column, $pos + 1);
    }

    public function createFormContainer(Container $container): ContainerWithOptions
    {
        $container = new ContainerWithOptions($container);
        $container->setOption('label', _('Participant'));

        foreach ($this->fields as $name => $field) {
            if (!$field->isVisible($this)) {
                continue;
            }
            $component = $field->createFormComponent($this);
            $container->addComponent($component, $name);
            $field->setFieldDefaultValue($component, $this);
        }
        return $container;
    }

    public function getPerson(): ?PersonModel
    {
        try {
            $app = $this->getModel();
            if (!$app) {
                return null;
            }
            return $app->person;
        } catch (CannotAccessModelException $exception) {
            return null;
        }
    }

    /**
     * @phpstan-param EventParticipantStatus $newState
     */
    public function updateState(EnumColumn $newState): void
    {
        $this->service->storeModel(['status' => $newState->value], $this->model);
    }

    public function getState(): EventParticipantStatus
    {
        return $this->model->status;
    }
}
