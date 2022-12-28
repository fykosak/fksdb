<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Containers\Models;

use Nette\DI\Container as DIContainer;
use Nette\Forms\Container;
use Nette\Forms\Controls\BaseControl;

/**
 * @note Code is copy+pasted from Nette\Forms\Controls\BaseControl.
 */
class ContainerWithOptions extends Container
{
    private array $options = [];
    protected ?DIContainer $container;

    public function __construct(?DIContainer $container = null)
    {
        $this->container = $container;
        if ($container) {
            $container->callInjects($this);
        }
    }

    /**
     * Sets user-specific option.
     * Options recognized by DefaultFormRenderer
     * - 'description' - textual or Html object description
     *
     * @param mixed $value
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
     * Returns user-specific option.
     * @param mixed $default value
     * @return mixed
     */
    final public function getOption(string $key, $default = null)
    {
        return $this->options[$key] ?? $default;
    }

    /**
     * Returns user-specific options.
     */
    final public function getOptions(): array
    {
        return $this->options;
    }

    public function setDisabled(bool $value = true): void
    {
        /** @var BaseControl $component */
        foreach ($this->getComponents() as $component) {
            $component->setDisabled($value);
        }
    }

    /**
     * @param mixed $value
     */
    public function setHtmlAttribute(string $name, $value = true): self
    {
        foreach ($this->getComponents() as $component) {
            $component->setHtmlAttribute($name, $value);
        }
        return $this;
    }
}
