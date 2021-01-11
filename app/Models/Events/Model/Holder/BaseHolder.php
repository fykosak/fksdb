<?php

namespace FKSDB\Models\Events\Model\Holder;

use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Models\Expressions\NeonSchemaException;
use FKSDB\Models\Expressions\NeonScheme;
use FKSDB\Models\Events\Machine\BaseMachine;
use FKSDB\Models\Events\Model\ExpressionEvaluator;
use FKSDB\Models\ORM\IModel;
use FKSDB\Models\ORM\IService;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Services\AbstractServiceSingle;
use FKSDB\Models\ORM\ServicesMulti\AbstractServiceMulti;
use Nette\InvalidArgumentException;
use Nette\InvalidStateException;
use Nette\Neon\Neon;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class BaseHolder {

    public const STATE_COLUMN = 'status';
    public const EVENT_COLUMN = 'event_id';
    private string $name;
    private ?string $description;
    private ExpressionEvaluator $evaluator;
    private DataValidator $validator;
    /** Relation to the primary holder's event.     */
    private ?IEventRelation $eventRelation;
    private ModelEvent $event;
    private string $label;
    private IService $service;
    private ?string $joinOn = null;
    private ?string $joinTo = null;
    private array $personIdColumns;
    private string $eventIdColumn;
    private Holder $holder;
    /** @var Field[] */
    private array $fields = [];
    private ?IModel $model = null;
    private array $paramScheme;
    private array $parameters;
    /** @var bool|callable */
    private $modifiable;
    /** @var bool|callable */
    private $visible;

    public function __construct(string $name) {
        $this->name = $name;
    }

    public function addField(Field $field): void {
        $field->setBaseHolder($this);
        $name = $field->getName();
        $this->fields[$name] = $field;
    }

    /**
     * @return Field[]
     */
    public function getFields(): array {
        return $this->fields;
    }

    public function getHolder(): Holder {
        return $this->holder;
    }

    public function setHolder(Holder $holder): void {
        $this->holder = $holder;
    }

    /**
     * @param bool|callable $modifiable
     * @return void
     */
    public function setModifiable($modifiable): void {
        $this->modifiable = $modifiable;
    }

    /**
     * @param bool|callable $visible
     * @return void
     */
    public function setVisible($visible): void {
        $this->visible = $visible;
    }

    public function setEventRelation(?IEventRelation $eventRelation): void {
        $this->eventRelation = $eventRelation;
    }

    public function getEvent(): ModelEvent {
        return $this->event;
    }

    /**
     * @param ModelEvent $event
     * @throws NeonSchemaException
     */
    private function setEvent(ModelEvent $event): void {
        $this->event = $event;
        $this->cacheParameters();
    }

    /**
     * @param ModelEvent $event
     * @throws NeonSchemaException
     */
    public function inferEvent(ModelEvent $event): void {
        if ($this->eventRelation instanceof IEventRelation) {
            $this->setEvent($this->eventRelation->getEvent($event));
        } else {
            $this->setEvent($event);
        }
    }

    public function getParamScheme(): array {
        return $this->paramScheme;
    }

    public function setParamScheme(array $paramScheme): void {
        $this->paramScheme = $paramScheme;
    }

    public function getEvaluator(): ExpressionEvaluator {
        return $this->evaluator;
    }

    public function setEvaluator(ExpressionEvaluator $evaluator): void {
        $this->evaluator = $evaluator;
    }

    public function getValidator(): DataValidator {
        return $this->validator;
    }

    public function setValidator(DataValidator $validator): void {
        $this->validator = $validator;
    }

    public function isVisible(): bool {
        return $this->getEvaluator()->evaluate($this->visible, $this);
    }

    public function isModifiable(): bool {
        return $this->getEvaluator()->evaluate($this->modifiable, $this);
    }

    public function &getModel(): IModel {
        if (!$this->model) {
            $this->model = $this->getService()->createNew(); // TODO!!!
        }
        return $this->model;
    }

    /**
     * @param int|IModel $model
     */
    public function setModel($model): void {
        if ($model instanceof IModel) {
            $this->model = $model;
        } elseif ($model) {
            $this->model = $this->service->findByPrimary($model);
        } else {
            $this->model = null;
        }
    }

    public function saveModel(): void {
        if ($this->getModelState() == BaseMachine::STATE_TERMINATED) {
            $this->service->dispose($this->getModel());
        } elseif ($this->getModelState() != BaseMachine::STATE_INIT) {
            $this->service->save($this->getModel());
        }
    }

    public function getModelState(): string {
        $model = $this->getModel();
        if ($model->isNew() && !$model[self::STATE_COLUMN]) {
            return BaseMachine::STATE_INIT;
        } else {
            return $model[self::STATE_COLUMN];
        }
    }

    public function setModelState(string $state): void {
        $this->getService()->updateModel($this->getModel(), [self::STATE_COLUMN => $state]);
    }

    public function updateModel(iterable $values, bool $alive = true): void {
        $values[self::EVENT_COLUMN] = $this->getEvent()->getPrimary();
        $this->getService()->updateModel($this->getModel(), $values, $alive);
    }

    public function getName(): string {
        return $this->name;
    }

    /**
     * @return IService|AbstractServiceSingle|AbstractServiceMulti
     */
    public function getService(): IService {
        return $this->service;
    }

    public function setService(IService $service): void {
        $this->service = $service;
    }

    public function getLabel(): string {
        return $this->label;
    }

    public function setLabel(string $label): void {
        $this->label = $label;
    }

    public function getDescription(): ?string {
        return $this->description;
    }

    public function setDescription(?string $description): void {
        $this->description = $description;
    }

    public function getJoinOn(): ?string {
        return $this->joinOn;
    }

    public function setJoinOn(?string $joinOn): void {
        $this->joinOn = $joinOn;
    }

    public function getJoinTo(): ?string {
        return $this->joinTo;
    }

    public function setJoinTo(?string $joinTo): void {
        $this->joinTo = $joinTo;
    }

    /**
     * @return string[]
     */
    public function getPersonIdColumns(): array {
        return $this->personIdColumns;
    }

    public function setPersonIdColumns(array $personIds): void {
        if (!$this->getService()) {
            throw new InvalidStateException('Call setService prior setting person IDs.');
        }

        $this->personIdColumns = [];
        foreach ($personIds as $personId) {
            $this->personIdColumns[] = $this->resolveColumnJoins($personId);
        }
    }

    public function getEventIdColumn(): string {
        return $this->eventIdColumn;
    }

    public function setEventIdColumn(string $eventId): void {
        $this->eventIdColumn = $this->resolveColumnJoins($eventId);
    }

    private function resolveColumnJoins(string $column): string {
        if (strpos($column, '.') === false && strpos($column, ':') === false) {
            $column = $this->getService()->getTable()->getName() . '.' . $column;
        }
        return $column;
    }

    /**
     * @param string $column
     * @return bool|mixed|string
     */
    public static function getBareColumn($column) {
        $column = str_replace(':', '.', $column);
        $pos = strrpos($column, '.');
        return $pos === false ? $column : substr($column, $pos + 1);
    }

    /**
     * @return Field[]
     */
    public function getDeterminingFields(): array {
        return array_filter($this->fields, function (Field $field): bool {
            return $field->isDetermining();
        });
    }

    public function createFormContainer(): ContainerWithOptions {
        $container = new ContainerWithOptions();
        $container->setOption('label', $this->getLabel());
        $container->setOption('description', $this->getDescription());

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
     * @return int|null  ID of a person associated with the application
     */
    public function getPersonId(): ?int {
        $personColumns = $this->getPersonIdColumns();
        if (!$personColumns) {
            return null;
        }
        $personColumn = reset($personColumns); //TODO we support only single person per model, so far
        $personColumn = self::getBareColumn($personColumn);
        $model = $this->getModel();
        return $model[$personColumn];
    }

    public function __toString(): string {
        return $this->name;
    }

    /*
     * Parameter handling
     */
    /**
     * @throws NeonSchemaException
     */
    private function cacheParameters(): void {
        $parameters = isset($this->getEvent()->parameters) ? $this->getEvent()->parameters : '';
        $parameters = $parameters ? Neon::decode($parameters) : [];
        $this->parameters = NeonScheme::readSection($parameters, $this->getParamScheme());
    }

    /**
     * @param string|int|int[]|string[] $name
     * @param null $default
     * @return mixed
     */
    public function getParameter($name, $default = null) {
        try {
            return $this->parameters[$name] ?? $default;
        } catch (InvalidArgumentException $exception) {
            throw new InvalidArgumentException("No parameter '$name' for event " . $this->getEvent() . '.', null, $exception);
        }
    }
}
