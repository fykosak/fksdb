<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components;

use FKSDB\Components\Grids\Components\Button\ButtonGroup;
use FKSDB\Components\Grids\Components\Container\ListRows;
use FKSDB\Models\ORM\ORMFactory;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

/**
 * @template TModel of \Fykosak\NetteORM\Model
 * @phpstan-extends BaseComponent<TModel>
 */
abstract class BaseList extends BaseComponent
{
    protected ORMFactory $reflectionFactory;
    /** @var ButtonGroup<TModel> */
    public ButtonGroup $buttons;
    /** @var ListRows<TModel> */
    public ListRows $rows;
    /** @var callable(TModel):string */
    protected $classNameCallback = null;

    public function __construct(Container $container, int $userPermission)
    {
        parent::__construct($container, $userPermission);
        $this->buttons = new ButtonGroup($this->container);
        $this->rows = new ListRows($this->container, new Title(null, ''));
        $this->addComponent($this->buttons, 'buttons');
        $this->addComponent($this->rows, 'rows');
    }

    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'list.latte';
    }

    public function render(): void
    {
        $this->template->classNameCallback = $this->classNameCallback;
        $this->template->title = $this->getComponent('title', false);
        parent::render();
    }

    abstract protected function configure(): void;

    /**
     * @phpstan-param BaseItem<TModel> $title
     */
    protected function setTitle(BaseItem $title): void
    {
        $this->addComponent($title, 'title');
    }

    /**
     * @phpstan-param BaseItem<TModel> $component
     */
    public function addRow(BaseItem $component, string $name): void
    {
        $this->rows->addComponent($component, $name);
    }

    /**
     * @phpstan-param BaseItem<TModel> $component
     */
    public function addButton(BaseItem $component, string $name): void
    {
        $this->buttons->addComponent($component, $name);
    }
}
