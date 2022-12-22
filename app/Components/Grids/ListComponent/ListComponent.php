<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\ListComponent;

use FKSDB\Components\Grids\ListComponent\Button\ButtonGroup;
use FKSDB\Components\Grids\ListComponent\Button\DefaultButton;
use FKSDB\Components\Grids\ListComponent\Row\ColumnsRow;
use FKSDB\Components\Grids\ListComponent\Row\ListGroupRow;
use FKSDB\Models\ORM\ORMFactory;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Fykosak\Utils\UI\Title;
use Nette\Application\UI\Presenter;
use Nette\ComponentModel\IContainer;
use Nette\DI\Container;

abstract class ListComponent extends BaseComponent implements IContainer
{
    protected ORMFactory $reflectionFactory;
    protected \Nette\ComponentModel\Container $buttons;
    /** @var callable */
    protected $classNameCallback = null;
    protected int $userPermission;

    public function __construct(Container $container, int $userPermission)
    {
        parent::__construct($container);
        $this->userPermission = $userPermission;
        $this->buttons = new ButtonGroup($this->container);
        $this->addComponent($this->buttons, 'buttons');
        $this->monitor(Presenter::class, function () {
            $this->configure();
        });
    }

    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'list.latte';
    }

    public function render(): void
    {
        $this->template->models = $this->getModels();
        $this->template->userPermission = $this->userPermission;
        $this->template->classNameCallback = $this->classNameCallback ?? fn() => '';
        $this->template->render($this->getTemplatePath());
    }

    abstract protected function getModels(): iterable;

    abstract protected function configure(): void;

    final public function createColumnsRow(string $name): ColumnsRow
    {
        $row = new ColumnsRow($this->container);
        $this->addComponent($row, $name);
        return $row;
    }

    final public function createListGroupRow(
        string $name,
        callable $modelToIterator,
        ?Title $title = null
    ): ListGroupRow {
        $row = new ListGroupRow($this->container, $modelToIterator, $title);
        $this->addComponent($row, $name);
        return $row;
    }

    final public function createDefaultButton(string $name, string $title, callable $callback): DefaultButton
    {
        $button = new DefaultButton($this->container, $title, $callback);
        $this->buttons->addComponent($button, $name);
        return $button;
    }
}
