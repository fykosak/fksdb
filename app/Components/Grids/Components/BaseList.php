<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components;

use FKSDB\Components\Grids\Components\Container\RowContainer;
use Nette\DI\Container;

/**
 * @phpstan-template TModel of \Fykosak\NetteORM\Model
 * @phpstan-extends BaseComponent<TModel>
 */
abstract class BaseList extends BaseComponent
{
    public \Nette\ComponentModel\Container $buttons;
    public \Nette\ComponentModel\Container $rows;
    /** @phpstan-var BaseItem<TModel> */
    public ?BaseItem $itemTitle = null;
    /** @phpstan-var callable(TModel):string */
    protected $classNameCallback = null;

    public function __construct(Container $container, int $userPermission)
    {
        parent::__construct($container, $userPermission);
        $this->buttons = new \Nette\ComponentModel\Container();
        $this->rows = new \Nette\ComponentModel\Container();
        $this->addComponent($this->buttons, 'buttons');
        $this->addComponent($this->rows, 'rows');
        $this->paginate = false;
    }

    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'list.latte';
    }

    public function render(): void
    {
        $this->template->classNameCallback = $this->classNameCallback;
        $this->template->title = $this->itemTitle;
        parent::render();
    }

    abstract protected function configure(): void;

    /**
     * @phpstan-template TComponent of BaseItem<TModel>
     * @phpstan-param TComponent $component
     * @phpstan-return TComponent
     */
    protected function setTitle(BaseItem $component): BaseItem
    {
        $this->addComponent($component, 'title');
        $this->itemTitle = $component;
        return $component;
    }

    /**
     * @phpstan-template TComponent of BaseItem<TModel>
     * @phpstan-param TComponent $component
     * @phpstan-return TComponent
     */
    public function addRow(BaseItem $component, string $name): BaseItem
    {
        $this->rows->addComponent($component, $name);
        return $component;
    }

    public function createRow(): \Nette\ComponentModel\Container
    {
        $component = new \Nette\ComponentModel\Container();
        $length = count($this->rows->getComponents());
        $this->rows->addComponent($component, 'row' . $length);
        return $component;
    }

    /**
     * @phpstan-template TComponent of BaseItem<TModel>
     * @phpstan-param TComponent $component
     * @phpstan-return TComponent
     */
    public function addButton(BaseItem $component, string $name): BaseItem
    {
        $this->buttons->addComponent($component, $name);
        return $component;
    }
}
