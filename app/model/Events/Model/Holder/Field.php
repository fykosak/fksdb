<?php

namespace FKSDB\Events\Model\Holder;

use FKSDB\Events\Machine\BaseMachine;
use FKSDB\Events\Model\ExpressionEvaluator;
use FKSDB\Components\Forms\Factories\Events\IFieldFactory;
use Nette\ComponentModel\IComponent;
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

    /** @return string|null */
    public function getLabel() {
        return $this->label;
    }

    /**
     * Field constructor.
     * @param string $name
     * @param string|null $label
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

    public function setBaseHolder(BaseHolder $baseHolder): void {
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
     * @param string|null $description
     * @return void
     */
    public function setDescription($description): void {
        $this->description = $description;
    }

    /* ** DETERMINING ** */
    /** @var bool */
    private $determining;

    public function isDetermining(): bool {
        return $this->determining;
    }

    public function setDetermining(bool $determining): void {
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
     * @param mixed $default
     * @return void
     */
    public function setDefault($default): void {
        $this->default = $default;
    }
    /* ** EVALUATOR ** */
    /** @var ExpressionEvaluator */
    private $evaluator;

    public function setEvaluator(ExpressionEvaluator $evaluator): void {
        $this->evaluator = $evaluator;
    }
    /* ** FACTORY ** */

    /** @var IFieldFactory */
    private $factory;

    public function setFactory(IFieldFactory $factory): void {
        $this->factory = $factory;
    }

    /*
     * Forms
     */
    public function createFormComponent(): IComponent {
        return $this->factory->createComponent($this);
    }

    public function setFieldDefaultValue(IComponent $component): void {
        $this->factory->setFieldDefaultValue($component, $this);
    }

    public function getMainControl(IComponent $component): IControl {
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
    public function setRequired($required): void {
        $this->required = $required;
    }
    /* ** MODIFIABLE ** */
    /** @var bool|callable */
    private $modifiable;

    public function isModifiable(): bool {
        return $this->getBaseHolder()->isModifiable() && $this->evaluator->evaluate($this->modifiable, $this);
    }

    /** @param bool|callable $modifiable */
    public function setModifiable($modifiable): void {
        $this->modifiable = $modifiable;
    }
    /* ** VISIBLE ** */
    /** @var bool|callable */
    private $visible;

    public function isVisible(): bool {
        return $this->evaluator->evaluate($this->visible, $this);
    }

    /**
     * @param callable|bool $visible
     * @return void
     */
    public function setVisible($visible): void {
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
