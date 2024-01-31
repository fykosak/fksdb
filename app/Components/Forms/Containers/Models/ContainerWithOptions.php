<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Containers\Models;

use Nette\DI\Container as DIContainer;
use Nette\Forms\Container;
use Nette\Forms\Controls\BaseControl;

class ContainerWithOptions extends Container
{
    /** @phpstan-var array<string,mixed> */
    private array $options = [];

    public bool $collapse = false;

    protected DIContainer $container;

    public function __construct(DIContainer $container)
    {
        $this->container = $container;
        $container->callInjects($this);
    }

    /**
     * Sets user-specific option.
     * Options recognized by DefaultFormRenderer
     * - 'description' - textual or Html object description
     *
     * @phpstan-template TNewValue
     * @phpstan-param TNewValue $value
     * @return static
     */
    public function setOption(string $key, $value): self
    {
        if ($value === null) {
            unset($this->options[$key]);
        } else {
            $this->options[$key] = $value;
        }
        return $this;
    }

    /**
     * Returns user-specific option
     * @phpstan-template TDefaultValue of mixed
     * @phpstan-param TDefaultValue $default
     * @phpstan-return TDefaultValue
     */
    final public function getOption(string $key, $default = null)
    {
        return $this->options[$key] ?? $default;
    }

    /**
     * Returns user-specific options.
     * @phpstan-return array<string,mixed>
     */
    final public function getOptions(): array
    {
        return $this->options;
    }

    public function setDisabled(bool $value = true): void
    {
        /** @var self|BaseControl $component */
        foreach ($this->getComponents() as $component) {
            $component->setDisabled($value);
        }
    }

    /**
     * @param mixed $value
     * @phpstan-return static
     */
    public function setHtmlAttribute(string $name, $value = true): self
    {
        /** @var self|BaseControl $component */
        foreach ($this->getComponents() as $component) {
            $component->setHtmlAttribute($name, $value);
        }
        return $this;
    }
}
