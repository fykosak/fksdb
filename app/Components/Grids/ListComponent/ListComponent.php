<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\ListComponent;

use FKSDB\Components\Grids\ListComponent\Button\DefaultButton;
use FKSDB\Components\Grids\ListComponent\Row\ColumnsRow;
use FKSDB\Components\Grids\ListComponent\Row\ListGroupRow;
use FKSDB\Components\Grids\ListComponent\Row\ORMRow;
use FKSDB\Components\Grids\ListComponent\Row\RendererRow;
use FKSDB\Models\ORM\ORMFactory;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Nette\Application\UI\Presenter;
use Nette\ComponentModel\IContainer;
use Nette\DI\Container;

abstract class ListComponent extends BaseComponent implements IContainer
{
    protected ORMFactory $reflectionFactory;
    protected \Nette\ComponentModel\Container $buttons;
    /** @var callable */
    protected $classNameCallback = null;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->buttons = new \Nette\ComponentModel\Container();
        $this->addComponent($this->buttons, 'buttons');
        $this->monitor(Presenter::class, function () {
            $this->configure();
        });
    }

    public function render(): void
    {
        $this->template->models = $this->getModels();
        $this->template->classNameCallback = $this->classNameCallback ?? fn() => '';
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'list.latte');
    }

    abstract protected function getModels(): iterable;

    abstract protected function configure(): void;

    final public function createReferencedRow(string $name): ORMRow
    {
        $row = new ORMRow($this->container, $name);
        $this->addComponent($row, str_replace('.', '__', $name));
        return $row;
    }

    final public function createRendererRow(string $name, callable $renderer): RendererRow
    {
        $row = new RendererRow($this->container, $renderer);
        $this->addComponent($row, $name);
        return $row;
    }


    final public function createColumnsRow(string $name): ColumnsRow
    {
        $row = new ColumnsRow($this->container);
        $this->addComponent($row, $name);
        return $row;
    }

    final public function createListGroupRow(string $name, callable $modelToIterator): ListGroupRow
    {
        $row = new ListGroupRow($this->container);
        $this->addComponent($row, $name);
        $row->setModelToIterator($modelToIterator);
        return $row;
    }

    final public function createDefaultButton(string $name, string $title, callable $callback): DefaultButton
    {
        $button = new DefaultButton($this->container, $title, $callback);
        $this->buttons->addComponent($button, $name);
        return $button;
    }
}
