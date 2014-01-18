<?php

namespace Events\Model;

use Events\Machine\BaseMachine;
use FKSDB\Components\Forms\Factories\Events\IFieldFactory;
use Nette\ComponentModel\Component;
use Nette\Forms\Container;
use Nette\FreezableObject;
use Nette\InvalidStateException;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class Field extends FreezableObject {

    /**
     * @var BaseHolder
     */
    private $baseHolder;

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
     * @var boolean
     */
    private $determining;

    /**
     * @var boolean|callable
     */
    private $required;

    /**
     * @var boolean|callable
     */
    private $modifiable;

    /**
     * @var boolean|callable
     */
    private $visible;

    /**
     * @var IFieldFactory
     */
    private $factory;

    function __construct($name, $label) {
        $this->name = $name;
        $this->label = $label;
    }

    /*
     * Accessors
     */

    public function getBaseHolder() {
        return $this->baseHolder;
    }

    public function setBaseHolder(BaseHolder $baseHolder) {
        $this->updating();
        $this->baseHolder = $baseHolder;
    }

    public function getName() {
        return $this->name;
    }

    public function getLabel() {
        return $this->label;
    }

    public function getDescription() {
        return $this->description;
    }

    public function setDescription($description) {
        $this->updating();
        $this->description = $description;
    }

    public function isDetermining() {
        return $this->determining;
    }

    public function setDetermining($determining) {
        $this->updating();
        $this->determining = $determining;
    }

    public function setRequired($required) {
        $this->updating();
        $this->required = $required;
    }

    public function setModifiable($modifiable) {
        $this->updating();
        $this->modifiable = $modifiable;
    }

    public function setVisible($visible) {
        $this->updating();
        $this->visible = $visible;
    }

    public function setFactory(IFieldFactory $factory) {
        $this->updating();
        $this->factory = $factory;
    }

    /*
     * Forms
     */

    public function createFormComponent(BaseMachine $machine, Container $container) {
        return $this->factory->create($this, $machine, $container);
    }

    public function getMainControl(Component $component) {
        return $this->factory->getMainControl($component);
    }

    /*
     * "Runtime" operations
     */

    public function isVisible(BaseMachine $machine) {
        return $this->evalCondition($this->visible, $machine);
    }

    public function isRequired(BaseMachine $machine) {
        return $this->evalCondition($this->required, $machine);
    }

    public function isModifiable(BaseMachine $machine) {
        return $this->getBaseHolder()->isModifiable($machine) && $this->evalCondition($this->modifiable, $machine);
    }

    public function getValue() {
        $model = $this->getBaseHolder()->getModel();
        return $model ? $model[$this->name] : null;
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

    public function __toString() {
        return "{$this->baseHolder}.{$this->name}";
    }

}
