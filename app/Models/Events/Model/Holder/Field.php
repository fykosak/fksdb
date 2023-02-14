<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Model\Holder;

use FKSDB\Components\Forms\Factories\Events\FieldFactory;
use Nette\Forms\Controls\BaseControl;

class Field
{
    public string $name;
    public ?string $label;
    public ?string $description;
    public BaseHolder $holder;
    private FieldFactory $factory;
    /** @var mixed */
    private $default;
    /** @var bool|callable */
    private $required;
    /** @var bool|callable */
    private $modifiable;
    /** @var bool|callable */
    private $visible;

    public function __construct(string $name, ?string $label)
    {
        $this->name = $name;
        $this->label = $label;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /** @return mixed */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @param mixed $default
     */
    public function setDefault($default): void
    {
        $this->default = $default;
    }

    public function setFactory(FieldFactory $factory): void
    {
        $this->factory = $factory;
    }

    /*
     * Forms
     */
    public function createFormComponent(): BaseControl
    {
        return $this->factory->createComponent($this);
    }

    public function setFieldDefaultValue(BaseControl $control): void
    {
        $this->factory->setFieldDefaultValue($control, $this);
    }

    /* ********* "Runtime" operations *********     */

    public function isRequired(): bool
    {
        if (is_bool($this->required)) {
            return $this->required;
        }
        return ($this->required)($this->holder);
    }

    /** @param bool|callable $required */
    public function setRequired($required): void
    {
        $this->required = $required;
    }

    /* ** MODIFIABLE ** */

    public function isModifiable(): bool
    {
        if (!$this->holder->isModifiable()) {
            return false;
        }
        if (is_bool($this->modifiable)) {
            return $this->modifiable;
        }
        return ($this->modifiable)($this->holder);
    }

    /** @param bool|callable $modifiable */
    public function setModifiable($modifiable): void
    {
        $this->modifiable = $modifiable;
    }

    /* ** VISIBLE ** */

    public function isVisible(): bool
    {
        if (is_bool($this->visible)) {
            return $this->visible;
        }
        return ($this->visible)($this->holder);
    }

    /**
     * @param callable|bool $visible
     */
    public function setVisible($visible): void
    {
        $this->visible = $visible;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        $model = $this->holder->getModel();
        if (isset($this->holder->data[$this->name])) {
            return $this->holder->data[$this->name];
        }
        if ($model) {
            if (isset($model[$this->name])) {
                return $model[$this->name];
            }
        } else {
            return $this->getDefault();
        }
        return null;
    }

    public function __toString(): string
    {
        return "$this->holder.$this->name";
    }
}
