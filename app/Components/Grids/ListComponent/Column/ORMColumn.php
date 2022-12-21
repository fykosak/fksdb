<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\ListComponent\Column;

use FKSDB\Components\Controls\ColumnPrinter\ColumnPrinterComponent;
use Fykosak\NetteORM\Model;
use Nette\DI\Container;

class ORMColumn extends Column
{
    protected string $factory;

    public function __construct(Container $container, string $factory)
    {
        parent::__construct($container);
        $this->factory = $factory;
    }

    public function render(Model $model, int $userPermission): void
    {
        $this->template->name = $this->factory;
        parent::render($model, $userPermission);
    }

    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'orm.latte';
    }

    protected function createComponentPrinter(): ColumnPrinterComponent
    {
        return new ColumnPrinterComponent($this->getContext());
    }
}
