<?php

namespace Events\Model\Holder;

use Events\Machine\BaseMachine;
use Events\Model\ExpressionEvaluator;
use FKS\Components\Forms\Containers\ContainerWithOptions;
use FKS\Config\NeonScheme;
use ModelEvent;
use Nette\Diagnostics\Debugger;
use Nette\Forms\Container;
use Nette\FreezableObject;
use Nette\InvalidArgumentException;
use Nette\InvalidStateException;
use Nette\Utils\Neon;
use ORM\IModel;
use ORM\IService;

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
    private $fields = array();

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
     * @var ISecondaryResolutionStrategy
     */
    private $secondaryResolution;

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

    function __construct($name) {
        $this->name = $name;
    }

    public function addField(Field $field) {
        $this->updating();
        $field->setBaseHolder($this);
        $field->freeze();

        $name = $field->getName();
        $this->fields[$name] = $field;
    }

    public function getFields() {
        return $this->fields;
    }

    public function getHolder() {
        return $this->holder;
    }

    public function setHolder(Holder $holder) {
        $this->updating();
        $this->holder = $holder;
    }

    public function setModifiable($modifiable) {
        $this->updating();
        $this->modifiable = $modifiable;
    }

    public function setVisible($visible) {
        $this->updating();
        $this->visible = $visible;
    }

    public function setEventRelation(IEventRelation $eventRelation = null) {
        $this->eventRelation = $eventRelation;
    }

    public function getEvent() {
        return $this->event;
    }

    private function setEvent(ModelEvent $event) {
        $this->updating();
        $this->event = $event;
        $this->cacheParameters();
    }

    public function inferEvent(ModelEvent $event) {
        if ($this->eventRelation instanceof IEventRelation) {
            $this->setEvent($this->eventRelation->getEvent($event));
        } else {
            $this->setEvent($event);
        }
    }

    public function getParamScheme() {
        return $this->paramScheme;
    }

    public function setParamScheme($paramScheme) {
        $this->updating();
        $this->paramScheme = $paramScheme;
    }

    public function getEvaluator() {
        return $this->evaluator;
    }

    public function setEvaluator(ExpressionEvaluator $evaluator) {
        $this->evaluator = $evaluator;
    }

    public function getValidator() {
        return $this->validator;
    }

    public function setValidator(DataValidator $validator) {
        $this->updating();
        $this->validator = $validator;
    }

    public function isVisible() {
        return $this->evaluator->evaluate($this->visible, $this);
    }

    public function isModifiable() {
        return $this->evaluator->evaluate($this->modifiable, $this);
    }

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

    public function setModelState($state) {
        $this->getService()->updateModel($this->getModel(), array(self::STATE_COLUMN => $state));
    }

    public function updateModel($values) {
        $values[self::EVENT_COLUMN] = $this->getEvent()->getPrimary();
        Debugger::barDump($this->getModel());
        Debugger::barDump($values);
        Debugger::barDump($this->getService());

        $this->getService()->updateModel($this->getModel(), $values);
    }

    public function resolveMultipleSecondaries($conflicts) {
        if (!$this->secondaryResolution) {
            throw new SecondaryModelConflictException($this->getModel(), $conflicts);
        }
        $this->secondaryResolution->resolve($this->getModel(), $conflicts);
    }

    public function getName() {
        return $this->name;
    }

    public function getService() {
        return $this->service;
    }

    public function setService(IService $service) {
        $this->updating();
        $this->service = $service;
    }

    public function getLabel() {
        return $this->label;
    }

    public function setLabel($label) {
        $this->updating();
        $this->label = $label;
    }

    public function getDescription() {
        return $this->description;
    }

    public function setDescription($description) {
        $this->updating();
        $this->description = $description;
    }

    public function getJoinOn() {
        return $this->joinOn;
    }

    public function setJoinOn($joinOn) {
        $this->updating();
        $this->joinOn = $joinOn;
    }

    public function getJoinTo() {
        return $this->joinTo;
    }

    public function setJoinTo($joinTo) {
        $this->updating();
        $this->joinTo = $joinTo;
    }

    public function getPersonIds() {
        return $this->personIds;
    }

    public function setPersonIds($personIds) {
        $this->updating();
        if (!$this->getService()) {
            throw new InvalidStateException('Call serService prior setting person IDs.');
        }

        $this->personIds = array();
        foreach ($personIds as $personId) {
            $this->personIds[] = $this->resolveColumnJoins($personId);
        }
    }

    public function getEventId() {
        return $this->eventId;
    }

    public function setEventId($eventId) {
        $this->eventId = $this->resolveColumnJoins($eventId);
    }

    private function resolveColumnJoins($column) {
        if (strpos($column, '.') === false && strpos($column, ':') === false) {
            $column = $this->getService()->getTable()->getName() . '.' . $column;
        }
        return $column;
    }

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
     * @return Container
     */
    public function createFormContainer(BaseMachine $machine) {
        $container = new ContainerWithOptions();
        $container->setOption('label', $this->getLabel());
        $container->setOption('description', $this->getDescription());

        foreach ($this->fields as $name => $field) {
            if (!$field->isVisible($machine)) {
                continue;
            }
            $components = $field->createFormComponent($machine, $container);
            if (!is_array($components)) {
                $components = array($components);
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

    public function __toString() {
        return $this->name;
    }

    /*
     * Parameter handling
     */

    private function cacheParameters() {
        $parameters = isset($this->getEvent()->parameters) ? $this->getEvent()->parameters : '';
        $parameters = $parameters ? Neon::decode($parameters) : array();
        $this->parameters = NeonScheme::readSection($parameters, $this->getParamScheme());
    }

    public function getParameter($name, $default = null) {
        $args = func_get_args();
        array_unshift($args, $this->parameters);
        try {
            $result = call_user_func_array('Nette\Utils\Arrays::get', $args);
            return $result;
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException("No parameter '$name' for event " . $this->getEvent() . ".", null, $e);
        }
    }

}
