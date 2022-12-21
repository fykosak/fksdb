<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\ListComponent\Row;

use FKSDB\Components\Controls\ColumnPrinter\ColumnPrinterComponent;
use Fykosak\NetteORM\Model;
use Nette\DI\Container;

class ORMTemplateRow extends Row
{
    protected string $templateString;

    public function __construct(Container $container, string $templateString)
    {
        parent::__construct($container);
        $this->templateString = $templateString;
    }

    public function render(Model $model, int $userPermission): void
    {
        $this->template->templateString = $this->templateString;
        parent::render($model, $userPermission);
    }

    protected function createComponentPrinter(): ColumnPrinterComponent
    {
        return new ColumnPrinterComponent($this->getContext());
    }

    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'ormTemplate.latte';
    }
}
