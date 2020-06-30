<?php

namespace FKSDB\Events\Model\Holder;

use FKSDB\Events\Machine\BaseMachine;
use FKSDB\Events\Model\ExpressionEvaluator;
use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Config\NeonSchemaException;
use FKSDB\Config\NeonScheme;
use FKSDB\ORM\AbstractServiceMulti;
use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\IModel;
use FKSDB\ORM\IService;
use FKSDB\ORM\Models\ModelEvent;
use Nette\InvalidArgumentException;
use Nette\InvalidStateException;
use Nette\Neon\Neon;
use Nette\Utils\Arrays;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class BaseHolder {

    const STATE_COLUMN = 'status';
    const EVENT_COLUMN = 'event_id';

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $label;

    /**
     * @var string
     */
    private $description;

    /**
     * @var IService
     */
    private $service;

    /**
     * @var string
     */
    private $joinOn;

    /**
     * @var string
     */
    private $joinTo;

    /**
     * @var string[]
     */
    private $personIds;

    /**
     * @var string
     */
    private $eventId;

    /**
     * @var Holder
     */
    private $holder;

    /**
     * @var bool|callable
     */
    private $modifiable;

    /**
     * @var bool|callable
     */
    private $visible;

    /**
     * @var Field[]
     */
    private $fields = [];

    /**
     * @var IModel
     */
    private $model;

    /**
     * Relation to the primary holder's event.
     *
     * @var IEventRelation|null
     */
    private $eventRelation;

    /**
     * @var ModelEvent
     */
    private $event;

    /**
     * @var array
     */
    private $paramScheme;

    /**
     * @var array
     */
    private $parameters;

    /**
     * @var ExpressionEvaluator
     */
    private $evaluator;

    /**
     * @var DataValidator
     */
    private $validator;

    /**
     * BaseHolder constructor.
     * @param $name
     */
    public function __construct(string $name) {
        $this->name = $name;
    }

    /**
     * @param Field $field
     * @return void
     */
    public function addField(Field $field) {
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

    /**
     * @param Holder $holder
     * @return void
     */
    public function setHolder(Holder $holder) {
        $this->holder = $holder;
    }

    /**
     * @param $modifiable
     * @return void
     */
    public function setModifiable($modifiable) {
        $this->modifiable = $modifiable;
    }

    /**
     * @param $visible
     * @return void
     */
    public function setVisible($visible) {
        $this->visible = $visible;
    }

    /**
     * @param IEventRelation|null $eventRelation
     */
    public function setEventRelation(IEventRelation $eventRelation = null) {
        $this->eventRelation = $eventRelation;
    }

    public function getEvent(): ModelEvent {
        return $this->event;
    }

    /**
     * @param ModelEvent $event
     * @throws NeonSchemaException
     */
    private function setEvent(ModelEvent $event) {
        $this->event = $event;
        $this->cacheParameters();
    }

    /**
     * @param ModelEvent $event
     * @throws NeonSchemaException
     */
    public function inferEvent(ModelEvent $event) {
        if ($this->eventRelation instanceof IEventRelation) {
            $this->setEvent($this->eventRelation->getEvent($event));
        } else {
            $this->setEvent($event);
        }
    }

    /**
     * @return array
     */
    public function getParamScheme() {
        return $this->paramScheme;
    }

    /**
     * @param $paramScheme
     * @return void
     */
    public function setParamScheme($paramScheme) {
        $this->paramScheme = $paramScheme;
    }

    /**
     * @return ExpressionEvaluator
     */
    public function getEvaluator() {
        return $this->evaluator;
    }

    /**
     * @param ExpressionEvaluator $evaluator
     * @return void
     */
    public function setEvaluator(ExpressionEvaluator $evaluator) {
        $this->evaluator = $evaluator;
    }

    public function getValidator(): DataValidator {
        return $this->validator;
    }

    /**
     * @param DataValidator $validator
     * @return void
     */
    public function setValidator(DataValidator $validator) {
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
    public function setModel($model) {
        if ($model instanceof IModel) {
            $this->model = $model;
        } elseif ($model) {
            $this->model = $this->service->findByPrimary($model);
        } else {
            $this->model = null;
        }
    }

    /**
     * @return void
     */
    public function saveModel() {
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

    /**
     * @param string $state
     * @return void
     */
    public function setModelState(string $state) {
        $this->getService()->updateModel($this->getModel(), [self::STATE_COLUMN => $state]);
    }

    /**
     * @param $values
     * @param bool $alive
     */
    public function updateModel($values, $alive = true) {
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

    /**
     * @param IService $service
     * @return void
     */
    public function setService(IService $service) {
        $this->service = $service;
    }

    /**
     * @return string
     */
    public function getLabel() {
        return $this->label;
    }

    /**
     * @param string $label
     */
    public function setLabel($label) {
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description) {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getJoinOn() {
        return $this->joinOn;
    }

    /**
     * @param string $joinOn
     */
    public function setJoinOn($joinOn) {
        $this->joinOn = $joinOn;
    }

    /**
     * @return string
     */
    public function getJoinTo() {
        return $this->joinTo;
    }

    /**
     * @param string $joinTo
     */
    public function setJoinTo($joinTo) {
        $this->joinTo = $joinTo;
    }

    /**
     * @return string[]
     */
    public function getPersonIds() {
        return $this->personIds;
    }

    /**
     * @param $personIds
     * @return void
     */
    public function setPersonIds($personIds) {
        if (!$this->getService()) {
            throw new InvalidStateException('Call serService prior setting person IDs.');
        }

        $this->personIds = [];
        foreach ($personIds as $personId) {
            $this->personIds[] = $this->resolveColumnJoins($personId);
        }
    }

    /**
     * @return string
     */
    public function getEventId() {
        return $this->eventId;
    }

    /**
     * @param $eventId
     * @return void
     */
    public function setEventId($eventId) {
        $this->eventId = $this->resolveColumnJoins($eventId);
    }

    private function resolveColumnJoins(string $column): string {
        if (strpos($column, '.') === false && strpos($column, ':') === false) {
            $column = $this->getService()->getTable()->getName() . '.' . $column;
        }
        return $column;
    }

    /**
     * @param $column
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
        return array_filter($this->fields, function (Field $field) {
            return $field->isDetermining();
        });
    }

    public function createFormContainer(BaseMachine $machine): ContainerWithOptions {
        $container = new ContainerWithOptions();
        $container->setOption('label', $this->getLabel());
        $container->setOption('description', $this->getDescription());

        foreach ($this->fields as $name => $field) {
            if (!$field->isVisible()) {
                continue;
            }
            $components = $field->createFormComponent($machine, $container);
            if (!is_array($components)) {
                $components = [$components];
            }
            $i = 0;
            foreach ($components as $component) {
                $componentName = ($i == 0) ? $name : "{$name}_{$i}";
                $container->addComponent($component, $componentName);
                ++$i;
            }
        }

        return $container;
    }

    /**
     * @return int|null  ID of a person associated with the application
     */
    public function getPersonId() {
        $personColumns = $this->getPersonIds();
        if (!$personColumns) {
            return null;
        }
        $personColumn = reset($personColumns); //TODO we support only single person per model, so far
        $personColumn = self::getBareColumn($personColumn);
        $model = $this->getModel();
        return $model[$personColumn];
    }

    /**
     * @return string
     */
    public function __toString() {
        return $this->name;
    }

    /*
     * Parameter handling
     */
    /**
     * @throws NeonSchemaException
     */
    private function cacheParameters() {
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
            return Arrays::get($this->parameters, $name, $default);
        } catch (InvalidArgumentException $exception) {
            throw new InvalidArgumentException("No parameter '$name' for event " . $this->getEvent() . ".", null, $exception);
        }
    }

}
