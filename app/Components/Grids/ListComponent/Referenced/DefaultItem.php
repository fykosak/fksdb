<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\ListComponent\Referenced;

use FKSDB\Components\Controls\ColumnPrinter\ColumnPrinterComponent;
use FKSDB\Components\Grids\ListComponent\ItemComponent;
use Fykosak\NetteORM\Model;
use Nette\DI\Container;

class DefaultItem extends ItemComponent
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
        return __DIR__ . DIRECTORY_SEPARATOR . 'default.latte';
    }

    protected function createComponentPrinter(): ColumnPrinterComponent
    {
        return new ColumnPrinterComponent($this->getContext());
    }
}
