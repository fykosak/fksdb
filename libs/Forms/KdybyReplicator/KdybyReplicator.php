<?php

declare(strict_types=1);

namespace Kdyby\Extension\Forms\Replicator;

use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\ComponentModel\Component;
use Nette\ComponentModel\IComponent;
use Nette\Forms\Container;
use Nette\Forms\ControlGroup;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\IControl;
use Nette\Forms\ISubmitterControl;
use Nette\InvalidArgumentException;
use Nette\MemberAccessException;
use Nette\Utils\Arrays;

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file
 * license.md that was distributed with this source code.
 * @author Filip Procházka <filip@prochazka.su>
 * @author Jan Tvrdík
 *
 * @method Form getForm()
 */
class Replicator extends Container
{
    public bool $forceDefault;
    public int $createDefault;
    /** @var callable */
    protected $factoryCallback;
    private bool $submittedBy = false;
    private array $created = [];
    private array $httpPost;
    private \Nette\DI\Container $container;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        callable $factory,
        \Nette\DI\Container $container,
        int $createDefault = 0,
        bool $forceDefault = false
    ) {
        $this->container = $container;
        $this->monitor(Presenter::class);
        $this->factoryCallback = $factory;
        $this->createDefault = $createDefault;
        $this->forceDefault = $forceDefault;
    }

    public function setFactory(callable $factory): void
    {
        $this->factoryCallback = $factory;
    }

    /**
     * Magical component factory
     */
    protected function attached(IComponent $obj): void
    {
        parent::attached($obj);

        if (!$obj instanceof Presenter) {
            return;
        }

        $this->loadHttpData();
        $this->createDefault();
    }


    public function getContainers(bool $recursive = false): \Iterator
    {
        return $this->getComponents($recursive, Container::class);
    }

    public function getButtons(bool $recursive = false): \Iterator
    {
        return $this->getComponents($recursive, ISubmitterControl::class);
    }


    /**
     * Magical component factory
     */
    protected function createComponent(string $name): ?IComponent
    {
        $container = new ContainerWithOptions($this->container);
        $container->currentGroup = $this->currentGroup;
        $this->addComponent($container, $name, $this->getFirstControlName());

        ($this->factoryCallback)($container);

        return $this->created[$container->name] = $container;
    }

    private function getFirstControlName(): ?string
    {
        /** @var Component[] $controls */
        $controls = iterator_to_array($this->getComponents(false, IControl::class));
        $firstControl = reset($controls);
        return $firstControl ? $firstControl->name : null;
    }

    public function isSubmittedBy(): bool
    {
        if ($this->submittedBy) {
            return true;
        }
        foreach ($this->getButtons(true) as $button) {
            if ($button->isSubmittedBy()) {
                return $this->submittedBy = true;
            }
        }
        return false;
    }


    /**
     * Create new container
     *
     * @param string|int $name
     * @throws InvalidArgumentException
     */
    public function createOne($name = null): ContainerWithOptions
    {
        if (!isset($name)) {
            $names = array_keys(iterator_to_array($this->getContainers()));
            $name = $names ? max($names) + 1 : 0;
        }

        // Container is overriden, therefore every request for getComponent($name, false) would return container
        if (isset($this->created[$name])) {
            throw new InvalidArgumentException("Container with name '$name' already exists.");
        }

        return $this->getComponent($name); // @phpstan-ignore-line
    }


    /**
     * @param array|\Traversable $values
     * @return Container|Replicator
     */
    public function setValues($values, bool $erase = false) // @phpstan-ignore-line
    {
        foreach ($values as $name => $value) {
            if ((is_array($value) || $value instanceof \Traversable) && !$this->getComponent((string)$name, false)) {
                $this->createOne($name);
            }
        }

        return parent::setValues($values, $erase);
    }


    /**
     * Loads data received from POST
     * @internal
     */
    protected function loadHttpData(): void
    {
        if (!$this->getForm()->isSubmitted()) {
            return;
        }
        $this->setValues($this->getHttpData());
    }


    /**
     * Creates default containers
     * @internal
     */
    protected function createDefault() // @phpstan-ignore-line
    {
        if (!$this->createDefault) {
            return;
        }

        if (!$this->getForm()->isSubmitted()) {
            foreach (range(0, $this->createDefault - 1) as $key) {
                $this->createOne($key);
            }
        } elseif ($this->forceDefault) {
            while (iterator_count($this->getContainers()) < $this->createDefault) {
                $this->createOne();
            }
        }
    }

    private function getHttpData(): array
    {
        if (!isset($this->httpPost)) {
            $path = explode(self::NAME_SEPARATOR, $this->lookupPath(\Nette\Forms\Form::class));
            $this->httpPost = Arrays::get($this->getForm()->getHttpData(), $path, null); // @phpstan-ignore-line
        }
        return $this->httpPost;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function remove(Container $container, bool $cleanUpGroups = false): void
    {
        if (!$container->parent === $this) { // @phpstan-ignore-line
            throw new InvalidArgumentException(
                'Given component ' . $container->name . ' is not children of ' . $this->getName() . '.'
            );
        }

        // to check if form was submitted by this one
        foreach ($container->getComponents(true, ISubmitterControl::class) as $button) {
            /** @var SubmitButton $button */
            if ($button->isSubmittedBy()) {
                $this->submittedBy = true;
                break;
            }
        }

        /** @var BaseControl[] $components */
        $components = $container->getComponents(true);
        $this->removeComponent($container);

        // reflection is required to hack form groups
        $groupReflection = new \ReflectionClass(ControlGroup::class);
        $controlsProperty = $groupReflection->getProperty('controls');
        $controlsProperty->setAccessible(true);

        // walk groups and clean then from removed components
        $affected = [];
        foreach ($this->getForm()->getGroups() as $group) {
            /** @var \SplObjectStorage $groupControls */
            $groupControls = $controlsProperty->getValue($group); // @phpstan-ignore-line

            foreach ($components as $control) {
                if ($groupControls->contains($control)) {
                    $groupControls->detach($control);

                    if (!in_array($group, $affected, true)) {
                        $affected[] = $group;
                    }
                }
            }
        }

        // remove affected & empty groups
        if ($cleanUpGroups && $affected) {
            foreach ($this->getForm()->getComponents(false, Container::class) as $container) {
                if ($index = array_search($container->currentGroup, $affected, true)) { // @phpstan-ignore-line
                    unset($affected[$index]);
                }
            }

            /** @var ControlGroup[] $affected */
            foreach ($affected as $group) {
                if (!$group->getControls() && in_array($group, $this->getForm()->getGroups(), true)) {
                    $this->getForm()->removeGroup($group);
                }
            }
        }
    }

    /**
     * Counts filled values, filtered by given names
     */
    public function countFilledWithout(array $components = [], array $subComponents = []): int
    {
        $httpData = array_diff_key($this->getHttpData(), array_flip($components));

        if (!$httpData) {
            return 0;
        }

        $rows = [];
        $subComponents = array_flip($subComponents);
        foreach ($httpData as $item) {
            $rows[] = array_filter(array_diff_key($item, $subComponents)) ?: false;
        }

        return count(array_filter($rows));
    }

    public function isAllFilled(array $exceptChildren = []): bool
    {
        $components = [];
        foreach ($this->getComponents(false, IControl::class) as $control) {
            /** @var BaseControl $control */
            $components[] = $control->getName();
        }

        foreach ($this->getContainers() as $container) {
            foreach ($container->getComponents(true, ISubmitterControl::class) as $button) {
                /** @var SubmitButton $button */
                $exceptChildren[] = $button->getName();
            }
        }

        $filled = $this->countFilledWithout($components, array_unique($exceptChildren));
        return $filled === iterator_count($this->getContainers());
    }

    private static ?string $registered = null;

    public static function register(\Nette\DI\Container $container, string $methodName = 'addDynamic'): void
    {
        if (self::$registered) {
            Container::extensionMethod(self::$registered, function () {
                throw new MemberAccessException();
            });
        }

        Container::extensionMethod(
            $methodName,
            function (\Nette\DI\Container $container, $name, $factory, $createDefault = 0) {
                return $container[$name] = new Replicator($factory, $container, $createDefault);
            }
        );

        if (self::$registered) {
            return;
        }

        SubmitButton::extensionMethod('addRemoveOnClick', function (SubmitButton $button, $callback = null) {
            $replicator = $button->lookup(self::class);
            $button->setValidationScope(null);
            $button->onClick[] = function (SubmitButton $button) use ($replicator, $callback) {
                /** @var Replicator $replicator */
                if (is_callable($callback)) {
                    $callback($replicator, $button->parent);
                }
                $replicator->remove($button->parent); // @phpstan-ignore-line
            };
            return $button;
        });

        SubmitButton::extensionMethod(
            'addCreateOnClick',
            function (SubmitButton $button, $allowEmpty = false, $callback = null) {
                $replicator = $button->lookup(self::class);
                $button->onClick[] = function () use ($replicator, $allowEmpty, $callback) {
                    /** @var Replicator $replicator */
                    if (!is_bool($allowEmpty)) {
                        $callback = $allowEmpty;
                        $allowEmpty = false;
                    }
                    if ($allowEmpty === false && $replicator->isAllFilled() === false) {
                        return;
                    }
                    $newContainer = $replicator->createOne();
                    if (is_callable($callback)) {
                        $callback($replicator, $newContainer);
                    }
                };
                return $button;
            }
        );

        self::$registered = $methodName;
    }
}
