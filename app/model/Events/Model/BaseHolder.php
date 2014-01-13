<?php

namespace Events\Model;

use Events\Machine\BaseMachine;
use Nette\Forms\Container;
use Nette\FreezableObject;
use Nette\InvalidStateException;
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
     * @var IService
     */
    private $service;

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

    public function isVisible(BaseMachine $machine) {
        return $this->evalCondition($this->visible, $machine);
    }

    public function isModifiable(BaseMachine $machine) {
        return $this->evalCondition($this->modifiable, $machine);
    }

    private function evalCondition($condition, BaseMachine $machine) {
        if (is_bool($condition)) {
            return $condition;
        } else if (is_callable($condition)) {
            return call_user_func($condition, $machine);
        } else {
            throw new InvalidStateException("Cannot evaluate condition $condition.");
        }
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
        } else {
            $this->model = $this->service->findByPrimary($model);
        }
    }

    public function saveModel() {
        if ($this->getModelState() == BaseMachine::STATE_TERMINATED) {
            $this->service->dispose($this->getModel());
        } else {
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
        $model = $this->getModel();
        $model[self::STATE_COLUMN] = $state;
    }

    public function updateModel($values) {
        $values[self::EVENT_COLUMN] = $this->getHolder()->getEvent()->getPrimary();
        $this->getService()->updateModel($this->getModel(), $values);
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

    /**
     * @return Field[]
     */
    public function getDeterminingFields() {
        return array_filter($this->fields, function(Field $field) {
                    return $field->isDetermining();
                });
    }

    /**
     * @return Container
     */
    public function createFormContainer(BaseMachine $machine) {
        $container = new Container();

        foreach ($this->fields as $name => $field) {
            //TODO implement self visibility, requirement and modifiability
            if (!$field->isVisible($machine)) {
                continue;
            }
            $component = $field->createFormComponent($machine, $container);
            $container->addComponent($component, $name);
        }

        return $container;
    }

    public function __toString() {
        return $this->name;
    }

}
