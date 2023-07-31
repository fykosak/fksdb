<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components;

use FKSDB\Components\Grids\Components\Button\ButtonGroup;
use FKSDB\Components\Grids\Components\Container\ListRows;
use FKSDB\Models\ORM\ORMFactory;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

/**
 * @template M of \Fykosak\NetteORM\Model
 * @phpstan-extends BaseComponent<M>
 */
abstract class BaseList extends BaseComponent
{
    protected ORMFactory $reflectionFactory;
    /** @var ButtonGroup<M> */
    public ButtonGroup $buttons;
    /** @var ListRows<M> */
    public ListRows $rows;
    /** @var callable(M):string */
    protected $classNameCallback = null;

    public function __construct(Container $container, int $userPermission)
    {
        parent::__construct($container, $userPermission);
        $this->buttons = new ButtonGroup($this->container, new Title(null, ''));
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
     * @phpstan-param BaseItem<M> $title
     */
    protected function setTitle(BaseItem $title): void
    {
        $this->addComponent($title, 'title');
    }

    /**
     * @phpstan-param BaseItem<M> $component
     */
    public function addRow(BaseItem $component, string $name): void
    {
        $this->rows->addComponent($component, $name);
    }

    /**
     * @phpstan-param BaseItem<M> $component
     */
    public function addButton(BaseItem $component, string $name): void
    {
        $this->buttons->addComponent($component, $name);
    }
}
