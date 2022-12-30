<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components;

use FKSDB\Components\Grids\Components\Button\ButtonGroup;
use FKSDB\Components\Grids\Components\Container\ListRows;
use FKSDB\Models\ORM\ORMFactory;
use Fykosak\Utils\UI\Title;
use Nette\Application\UI\Control;
use Nette\DI\Container;

abstract class ListComponent extends BaseListComponent
{
    protected ORMFactory $reflectionFactory;
    public ButtonGroup $buttons;
    public ListRows $rows;
    /** @var callable */
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

    protected function setTitle(Control $title): void
    {
        $this->addComponent($title, 'title');
    }

    protected function addRow(ItemComponent $component, string $name): void
    {
        $this->rows->addComponent($component, $name);
    }

    protected function addButton(ItemComponent $button, string $name): void
    {
        $this->buttons->addComponent($button, $name);
    }
}