<?php

namespace FKSDB\Events\Model\Holder;

use FKSDB\Events\Machine\BaseMachine;
use FKSDB\Events\Model\ExpressionEvaluator;
use FKSDB\Components\Forms\Factories\Events\IFieldFactory;
use Nette\ComponentModel\Component;
use Nette\ComponentModel\IComponent;
use Nette\Forms\Container;
use Nette\Forms\IControl;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class Field {
    /* ** NAME ** */
    /** @var string */
    private $name;

    public function getName(): string {
        return $this->name;
    }
    /* ** LABEL ** */

    /** @var string|null */
    private $label;

    /** @return string */
    public function getLabel() {
        return $this->label;
    }

    /**
     * Field constructor.
     * @param $name
     * @param $label
     */
    public function __construct(string $name, string $label = null) {
        $this->name = $name;
        $this->label = $label;
    }

    /*
     * Accessors
     */
    /* ** BASE HOLDER ** */
    /** @var BaseHolder */
    private $baseHolder;


    public function getBaseHolder(): BaseHolder {
        return $this->baseHolder;
    }

    /**
     * @param BaseHolder $baseHolder
     * @return void
     */
    public function setBaseHolder(BaseHolder $baseHolder) {
        $this->baseHolder = $baseHolder;
    }
    /* ** DESCRIPTION ** */
    /** @var string */
    private $description;

    /** @return string */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @param $description
     * @return void
     */
    public function setDescription($description) {
        $this->description = $description;
    }

    /* ** DETERMINING ** */
    /** @var bool */
    private $determining;

    public function isDetermining(): bool {
        return $this->determining;
    }

    /**
     * @param bool $determining
     * @return void
     */
    public function setDetermining(bool $determining) {
        $this->determining = $determining;
    }

    /* ** DEFAULT ** */
    /** @var mixed */
    private $default;

    /** @return mixed */
    public function getDefault() {
        return $this->default;
    }

    /**
     * @param $default
     * @return void
     */
    public function setDefault($default) {
        $this->default = $default;
    }
    /* ** EVALUATOR ** */
    /** @var ExpressionEvaluator */
    private $evaluator;

    /**
     * @param ExpressionEvaluator $evaluator
     * @return void
     */
    public function setEvaluator(ExpressionEvaluator $evaluator) {
        $this->evaluator = $evaluator;
    }
    /* ** FACTORY ** */

    /** @var IFieldFactory */
    private $factory;

    /**
     * @param IFieldFactory $factory
     * @return void
     */
    public function setFactory(IFieldFactory $factory) {
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
    public function createFormComponent(Container $container) {
        return $this->factory->create($this, $container);
    }

    /**
     * @param IComponent|Component $component
     * @return IControl
     */
    public function getMainControl(IComponent $component) {
        return $this->factory->getMainControl($component);
    }

    /* ********* "Runtime" operations *********     */
    /* ** REQUIRED ** */
    /** @var bool|callable */
    private $required;

    public function isRequired(): bool {
        return $this->evaluator->evaluate($this->required, $this);
    }

    /** @param bool|callable $required */
    public function setRequired($required) {
        $this->required = $required;
    }
    /* ** MODIFIABLE ** */
    /** @var bool|callable */
    private $modifiable;

    public function isModifiable(): bool {
        return $this->getBaseHolder()->isModifiable() && $this->evaluator->evaluate($this->modifiable, $this);
    }

    /** @param bool|callable $modifiable */
    public function setModifiable($modifiable) {
        $this->modifiable = $modifiable;
    }
    /* ** VISIBLE ** */
    /** @var bool|callable */
    private $visible;

    public function isVisible(): bool {
        return $this->evaluator->evaluate($this->visible, $this);
    }

    /**
     * @param $visible
     * @return void
     */
    public function setVisible($visible) {
        $this->visible = $visible;
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
        if (isset($model[$this->name])) {
            return $model[$this->name];
        }
        if ($this->getBaseHolder()->getModelState() == BaseMachine::STATE_INIT) {
            return $this->getDefault();
        }
        return null;
    }

    /**
     * @return string
     */
    public function __toString() {
        return "{$this->baseHolder}.{$this->name}";
    }

}
