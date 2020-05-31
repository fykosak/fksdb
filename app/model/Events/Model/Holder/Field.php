<?php

namespace FKSDB\Events\Model\Holder;

use FKSDB\Events\Machine\BaseMachine;
use FKSDB\Events\Model\ExpressionEvaluator;
use FKSDB\Components\Forms\Factories\Events\IFieldFactory;
use Nette\ComponentModel\Component;
use Nette\Forms\Container;
use Nette\Forms\IControl;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class Field {

    private string $name;

    private ?string $label;

    private BaseHolder $baseHolder;

    private ?string $description;

    private bool $determining;

    private ExpressionEvaluator $evaluator;

    private IFieldFactory $factory;

    /**
     * Field constructor.
     * @param $name
     * @param $label
     */
    public function __construct(string $name, string $label = null) {
        $this->name = $name;
        $this->label = $label;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getLabel(): ?string {
        return $this->label;
    }

    public function getBaseHolder(): BaseHolder {
        return $this->baseHolder;
    }

    public function setBaseHolder(BaseHolder $baseHolder): void {
        $this->baseHolder = $baseHolder;
    }

    public function getDescription(): ?string {
        return $this->description;
    }

    public function setDescription(?string $description): void {
        $this->description = $description;
    }

    public function isDetermining(): bool {
        return $this->determining;
    }

    public function setDetermining(bool $determining): void {
        $this->determining = $determining;
    }

    public function setEvaluator(ExpressionEvaluator $evaluator): void {
        $this->evaluator = $evaluator;
    }

    public function setFactory(IFieldFactory $factory): void {
        $this->factory = $factory;
    }

    /* ** DEFAULT ** */
    /** @var mixed */
    private $default;

    /** @return mixed */
    public function getDefault() {
        return $this->default;
    }

    /** @param $default */
    public function setDefault($default) {
        $this->default = $default;
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
     * @return IControl
     */
    public function getMainControl(Component $component) {
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

    /** @param callable|bool $visible */
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
