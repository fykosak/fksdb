<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Model\Holder;

use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\Expressions\NeonSchemaException;
use FKSDB\Models\Expressions\NeonScheme;
use FKSDB\Models\Events\Model\ExpressionEvaluator;
use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Machine\AbstractMachine;
use Fykosak\NetteORM\Model;
use Fykosak\NetteORM\Service;
use FKSDB\Models\ORM\ModelsMulti\Events\ModelMDsefParticipant;
use FKSDB\Models\ORM\ServicesMulti\ServiceMulti;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Nette\InvalidArgumentException;
use Nette\Neon\Neon;

class BaseHolder implements ModelHolder
{

    public const STATE_COLUMN = 'status';
    public const EVENT_COLUMN = 'event_id';
    public string $name;
    public ?string $description;
    private ExpressionEvaluator $evaluator;
    public DataValidator $validator;
    /** Relation to the primary holder's event.     */
    private ?EventRelation $eventRelation;
    public EventModel $event;
    public string $label;
    /** @var Service|ServiceMulti */
    public $service;
    public string $eventIdColumn;
    public Holder $holder;
    /** @var Field[] */
    private array $fields = [];
    private ?Model $model;
    public array $paramScheme;
    private array $parameters;
    /** @var bool|callable */
    private $modifiable;
    /** @var bool|callable */
    private $visible;

    public array $data = [];

    public function __construct(string $name)
    {
        $this->name = $name;
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

    public function setHolder(Holder $holder): void
    {
        $this->holder = $holder;
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

    public function setEventRelation(?EventRelation $eventRelation): void
    {
        $this->eventRelation = $eventRelation;
    }

    /**
     * @throws NeonSchemaException
     */
    private function setEvent(EventModel $event): void
    {
        $this->event = $event;
        $this->data[self::EVENT_COLUMN] = $this->event->getPrimary();
        $this->cacheParameters();
    }

    /**
     * @throws NeonSchemaException
     */
    public function inferEvent(EventModel $event): void
    {
        if ($this->eventRelation instanceof EventRelation) {
            $this->setEvent($this->eventRelation->getEvent($event));
        } else {
            $this->setEvent($event);
        }
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

    public function getModelState(): string
    {
        $model = $this->getModel();
        if (isset($this->data[self::STATE_COLUMN])) {
            return $this->data[self::STATE_COLUMN];
        }
        if ($model && $model[self::STATE_COLUMN]) {
            return $model[self::STATE_COLUMN];
        }

        return AbstractMachine::STATE_INIT;
    }

    public function setModelState(string $state): void
    {
        $this->data[self::STATE_COLUMN] = $state;
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

    public function setEventIdColumn(string $eventId): void
    {
        $this->eventIdColumn = $this->resolveColumnJoins($eventId);
    }

    private function resolveColumnJoins(string $column): string
    {
        if (strpos($column, '.') === false && strpos($column, ':') === false) {
            $column = $this->service->getTable()->getName() . '.' . $column;
        }
        return $column;
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
     * @param string|int $name
     * @param mixed $default
     * @return mixed
     */
    public function getParameter($name, $default = null)
    {
        try {
            return $this->parameters[$name] ?? $default;
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
        throw new NotImplementedException();
    }

    public function getState(): EnumColumn
    {
        throw new NotImplementedException();
    }
}
