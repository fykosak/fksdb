<?php

namespace Events\Model\Holder;

use Events\Machine\BaseMachine;
use Events\Model\ExpressionEvaluator;
use FKSDB\Components\Forms\Factories\Events\IFieldFactory;
use Nette\ComponentModel\Component;
use Nette\Forms\Container;
use Nette\FreezableObject;

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
     * @var mixed
     */
    private $default;

    /**
     * @var ExpressionEvaluator
     */
    private $evaluator;

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

    public function getDefault() {
        return $this->default;
    }

    public function setDefault($default) {
        $this->updating();
        $this->default = $default;
    }

    public function getEvaluator() {
        return $this->evaluator;
    }

    public function setEvaluator(ExpressionEvaluator $evaluator) {
        $this->evaluator = $evaluator;
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

    public function isVisible() {
        return $this->evaluator->evaluate($this->visible, $this);
    }

    public function isRequired() {
        return $this->evaluator->evaluate($this->required, $this);
    }

    public function isModifiable() {
        return $this->getBaseHolder()->isModifiable() && $this->evaluator->evaluate($this->modifiable, $this);
    }

    public function isSatisfied() {
        return $this->factory->isFieldSatisfied($this);
    }

    public function getValue() {
        $model = $this->getBaseHolder()->getModel();
        if (!isset($model[$this->name])) {
            if ($this->getBaseHolder()->getModelState() == BaseMachine::STATE_INIT) {
                return $this->getDefault();
            } else {
                return null;
            }
        } else {
            return $model[$this->name];
        }
    }

    public function __toString() {
        return "{$this->baseHolder}.{$this->name}";
    }

}
