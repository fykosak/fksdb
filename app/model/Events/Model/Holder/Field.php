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

    /**
     * Field constructor.
     * @param $name
     * @param $label
     */
    function __construct($name, $label) {
        $this->name = $name;
        $this->label = $label;
    }

    /*
     * Accessors
     */

    /**
     * @return BaseHolder
     */
    public function getBaseHolder() {
        return $this->baseHolder;
    }

    /**
     * @param BaseHolder $baseHolder
     */
    public function setBaseHolder(BaseHolder $baseHolder) {
        $this->updating();
        $this->baseHolder = $baseHolder;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getLabel() {
        return $this->label;
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
     * @return bool
     */
    public function isDetermining() {
        return $this->determining;
    }

    /**
     * @param $determining
     */
    public function setDetermining($determining) {
        $this->updating();
        $this->determining = $determining;
    }

    /**
     * @param $required
     */
    public function setRequired($required) {
        $this->updating();
        $this->required = $required;
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
     * @return mixed
     */
    public function getDefault() {
        return $this->default;
    }

    /**
     * @param $default
     */
    public function setDefault($default) {
        $this->updating();
        $this->default = $default;
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
     * @param IFieldFactory $factory
     */
    public function setFactory(IFieldFactory $factory) {
        $this->updating();
        $this->factory = $factory;
    }

    /*
     * Forms
     */

    /**
     * @param BaseMachine $machine
     * @param Container $container
     * @return mixed
     */
    public function createFormComponent(BaseMachine $machine, Container $container) {
        return $this->factory->create($this, $machine, $container);
    }

    /**
     * @param Component $component
     * @return \Nette\Forms\IControl
     */
    public function getMainControl(Component $component) {
        return $this->factory->getMainControl($component);
    }

    /*
     * "Runtime" operations
     */

    /**
     * @return mixed
     */
    public function isVisible() {
        return $this->evaluator->evaluate($this->visible, $this);
    }

    /**
     * @return mixed
     */
    public function isRequired() {
        return $this->evaluator->evaluate($this->required, $this);
    }

    /**
     * @return bool
     */
    public function isModifiable() {
        return $this->getBaseHolder()->isModifiable() && $this->evaluator->evaluate($this->modifiable, $this);
    }

    /**
     * @param DataValidator $validator
     * @return bool
     */
    public function validate(DataValidator $validator) {
        return $this->factory->validate($this, $validator);
    }

    /**
     * @return mixed|null
     */
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

    /**
     * @return string
     */
    public function __toString() {
        return "{$this->baseHolder}.{$this->name}";
    }

}
