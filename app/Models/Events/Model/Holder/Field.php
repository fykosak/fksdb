<?php

namespace FKSDB\Models\Events\Model\Holder;

use FKSDB\Components\Forms\Factories\Events\FieldFactory;
use FKSDB\Models\Events\Machine\BaseMachine;
use FKSDB\Models\Events\Model\ExpressionEvaluator;
use Nette\Forms\Controls\BaseControl;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class Field {

    private string $name;
    private bool $determining;
    private ?string $label;
    private ?string $description;
    private BaseHolder $baseHolder;
    private ExpressionEvaluator $evaluator;
    private FieldFactory $factory;

    /** @var mixed */
    private $default;
    /** @var bool|callable */
    private $required;
    /** @var bool|callable */
    private $modifiable;
    /** @var bool|callable */
    private $visible;

    public function __construct(string $name, ?string $label = null) {
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

    public function setEvaluator(ExpressionEvaluator $evaluator): void {
        $this->evaluator = $evaluator;
    }

    public function setFactory(FieldFactory $factory): void {
        $this->factory = $factory;
    }

    /*
     * Forms
     */
    public function createFormComponent(): BaseControl {
        return $this->factory->createComponent($this);
    }

    public function setFieldDefaultValue(BaseControl $control): void {
        $this->factory->setFieldDefaultValue($control, $this);
    }

    /* ********* "Runtime" operations *********     */

    public function isRequired(): bool {
        return $this->evaluator->evaluate($this->required, $this);
    }

    /** @param bool|callable $required */
    public function setRequired($required): void {
        $this->required = $required;
    }

    /* ** MODIFIABLE ** */

    public function isModifiable(): bool {
        return $this->getBaseHolder()->isModifiable() && (bool)$this->evaluator->evaluate($this->modifiable, $this);
    }

    /** @param bool|callable $modifiable */
    public function setModifiable($modifiable): void {
        $this->modifiable = $modifiable;
    }

    /* ** VISIBLE ** */

    public function isVisible(): bool {
        return (bool)$this->evaluator->evaluate($this->visible, $this);
    }

    /**
     * @param callable|bool $visible
     * @return void
     */
    public function setVisible($visible): void {
        $this->visible = $visible;
    }

    public function validate(DataValidator $validator): void {
        $this->factory->validate($this, $validator);
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

    public function __toString(): string {
        return "{$this->baseHolder}.{$this->name}";
    }
}
