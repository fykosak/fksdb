<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\ListComponent\Column;

use FKSDB\Components\Controls\ColumnPrinter\ColumnPrinterComponent;
use Fykosak\NetteORM\Model;
use Nette\DI\Container;

class ORMColumn extends Column
{
    protected string $name;

    public function __construct(Container $container, string $name)
    {
        parent::__construct($container);
        $this->name = $name;
    }

    public function render(Model $model): void
    {
        $this->template->className = $this->className;
        $this->template->model = $model;
        $this->template->name = $this->name;
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'orm.latte');
    }

    protected function createComponentPrinter(): ColumnPrinterComponent
    {
        return new ColumnPrinterComponent($this->getContext());
    }
}
