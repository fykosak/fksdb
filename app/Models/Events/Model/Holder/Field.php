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
    private FieldFactory $factory;
    /** @var mixed */
    private $default;
    /** @phpstan-var bool|(callable(BaseHolder):bool) */
    private $required;
    /** @phpstan-var bool|(callable(BaseHolder):bool) */
    private $modifiable;
    /** @phpstan-var bool|(callable(BaseHolder):bool) */
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
    public function createFormComponent(BaseHolder $holder): BaseControl
    {
        return $this->factory->createComponent($this, $holder);
    }

    public function setFieldDefaultValue(BaseControl $control, BaseHolder $holder): void
    {
        $this->factory->setFieldDefaultValue($control, $this, $holder);
    }

    public function isRequired(BaseHolder $holder): bool
    {
        if (is_callable($this->required)) {
            return ($this->required)($holder);
        }
        return (bool)$this->required;
    }

    /** @phpstan-param bool|(callable(BaseHolder):bool) $required */
    public function setRequired($required): void
    {
        $this->required = $required;
    }

    /* ** MODIFIABLE ** */

    public function isModifiable(BaseHolder $holder): bool
    {
        if (!$holder->isModifiable()) {
            return false;
        }
        if (is_callable($this->modifiable)) {
            return ($this->modifiable)($holder);
        }
        return (bool)$this->modifiable;
    }

    /** @phpstan-param bool|(callable(BaseHolder):bool) $modifiable */
    public function setModifiable($modifiable): void
    {
        $this->modifiable = $modifiable;
    }

    /* ** VISIBLE ** */

    public function isVisible(BaseHolder $holder): bool
    {
        if (is_callable($this->visible)) {
            return ($this->visible)($holder);
        }
        return $this->visible;
    }

    /**
     * @phpstan-param bool|(callable(BaseHolder):bool) $visible
     */
    public function setVisible($visible): void
    {
        $this->visible = $visible;
    }

    /**
     * @return mixed
     */
    public function getValue(BaseHolder $holder)
    {
        if (isset($holder->data[$this->name])) {
            return $holder->data[$this->name];
        }
        $model = $holder->getModel();
        if ($model) {
            if (isset($model[$this->name])) {
                return $model[$this->name];
            }
        } else {
            return $this->getDefault();
        }
        return null;
    }
}
