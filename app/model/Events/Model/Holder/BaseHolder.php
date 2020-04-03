<?php

namespace Events\Model\Holder;

use Events\Machine\BaseMachine;
use Events\Model\ExpressionEvaluator;
use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Config\NeonScheme;
use FKSDB\ORM\AbstractServiceMulti;
use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\IModel;
use FKSDB\ORM\IService;
use FKSDB\ORM\Models\ModelEvent;
use Nette\FreezableObject;
use Nette\InvalidArgumentException;
use Nette\InvalidStateException;
use Nette\Neon\Neon;
use Nette\Utils\Arrays;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class BaseHolder extends FreezableObject {

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
     * @var boolean|callable
     */
    private $modifiable;

    /**
     * @var boolean|callable
     */
    private $visible;

    /**
     * @var Field[]
     */
    private $fields = [];

    /**
     * @var \FKSDB\ORM\IModel
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
    function __construct($name) {
        $this->name = $name;
    }

    /**
     * @param Field $field
     */
    public function addField(Field $field) {
        $this->updating();
        $field->setBaseHolder($this);

        $name = $field->getName();
        $this->fields[$name] = $field;
    }

    /**
     * @return Field[]
     */
    public function getFields() {
        return $this->fields;
    }

    /**
     * @return Holder
     */
    public function getHolder() {
        return $this->holder;
    }

    /**
     * @param Holder $holder
     */
    public function setHolder(Holder $holder) {
        $this->updating();
        $this->holder = $holder;
    }

    /**
     * @param $modifiable
     */
    public function setModifiable($modifiable) {
        $this->updating();
        $this->modifiable = $modifiable;
    }

    /**
     * @param $visible
     */
    public function setVisible($visible) {
        $this->updating();
        $this->visible = $visible;
    }

    /**
     * @param IEventRelation|null $eventRelation
     */
    public function setEventRelation(IEventRelation $eventRelation = null) {
        $this->eventRelation = $eventRelation;
    }

    /**
     * @return ModelEvent
     */
    public function getEvent() {
        return $this->event;
    }

    /**
     * @param \FKSDB\ORM\Models\ModelEvent $event
     * @throws \FKSDB\Config\NeonSchemaException
     */
    private function setEvent(ModelEvent $event) {
        $this->updating();
        $this->event = $event;
        $this->cacheParameters();
    }

    /**
     * @param \FKSDB\ORM\Models\ModelEvent $event
     * @throws \FKSDB\Config\NeonSchemaException
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
     */
    public function setParamScheme($paramScheme) {
        $this->updating();
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
     */
    public function setEvaluator(ExpressionEvaluator $evaluator) {
        $this->evaluator = $evaluator;
    }

    /**
     * @return DataValidator
     */
    public function getValidator() {
        return $this->validator;
    }

    /**
     * @param DataValidator $validator
     */
    public function setValidator(DataValidator $validator) {
        $this->updating();
        $this->validator = $validator;
    }

    /**
     * @return mixed
     */
    public function isVisible() {
        return $this->evaluator->evaluate($this->visible, $this);
    }

    /**
     * @return mixed
     */
    public function isModifiable() {
        return $this->evaluator->evaluate($this->modifiable, $this);
    }

    /**
     * @return \FKSDB\ORM\IModel
     */
    public function & getModel() {
        if (!$this->model) {
            $this->model = $this->getService()->createNew();
        }
        return $this->model;
    }

    /**
     * @param int|IModel $model
     */
    public function setModel($model) {
        if ($model instanceof IModel) {
            $this->model = $model;
        } else if ($model) {
            $this->model = $this->service->findByPrimary($model);
        } else {
            $this->model = null;
        }
    }

    public function saveModel() {
        if ($this->getModelState() == BaseMachine::STATE_TERMINATED) {
            $this->service->dispose($this->getModel());
        } else if ($this->getModelState() != BaseMachine::STATE_INIT) {
            $this->service->save($this->getModel());
        }
    }

    /**
     * @return string
     */
    public function getModelState() {
        $model = $this->getModel();
        if ($model->isNew() && !$model[self::STATE_COLUMN]) {
            return BaseMachine::STATE_INIT;
        } else {
            return $model[self::STATE_COLUMN];
        }
    }

    /**
     * @param $state
     */
    public function setModelState($state) {
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

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return IService|AbstractServiceSingle|AbstractServiceMulti
     */
    public function getService() {
        return $this->service;
    }

    /**
     * @param \FKSDB\ORM\IService $service
     */
    public function setService(IService $service) {
        $this->updating();
        $this->service = $service;
    }

    /**
     * @return string
     */
    public function getLabel() {
        return $this->label;
    }

    /**
     * @param $label
     */
    public function setLabel($label) {
        $this->updating();
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @param $description
     */
    public function setDescription($description) {
        $this->updating();
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getJoinOn() {
        return $this->joinOn;
    }

    /**
     * @param $joinOn
     */
    public function setJoinOn($joinOn) {
        $this->updating();
        $this->joinOn = $joinOn;
    }

    /**
     * @return string
     */
    public function getJoinTo() {
        return $this->joinTo;
    }

    /**
     * @param $joinTo
     */
    public function setJoinTo($joinTo) {
        $this->updating();
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
     */
    public function setPersonIds($personIds) {
        $this->updating();
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
     */
    public function setEventId($eventId) {
        $this->eventId = $this->resolveColumnJoins($eventId);
    }

    /**
     * @param $column
     * @return string
     */
    private function resolveColumnJoins($column) {
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
    public function getDeterminingFields() {
        return array_filter($this->fields, function (Field $field) {
            return $field->isDetermining();
        });
    }

    /**
     * @param BaseMachine $machine
     * @return ContainerWithOptions
     */
    public function createFormContainer(BaseMachine $machine) {
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
     * @throws \FKSDB\Config\NeonSchemaException
     */
    private function cacheParameters() {
        $parameters = isset($this->getEvent()->parameters) ? $this->getEvent()->parameters : '';
        $parameters = $parameters ? Neon::decode($parameters) : [];
        $this->parameters = NeonScheme::readSection($parameters, $this->getParamScheme());
    }

    /**
     * @param $name
     * @param null $default
     * @return mixed
     */
    public function getParameter($name, $default = null) {
        try {
            return Arrays::get($this->parameters, [$name], $default);
        } catch (InvalidArgumentException $exception) {
            throw new InvalidArgumentException("No parameter '$name' for event " . $this->getEvent() . ".", null, $exception);
        }
    }

}
