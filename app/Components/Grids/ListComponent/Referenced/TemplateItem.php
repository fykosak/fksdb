<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\ListComponent\Referenced;

use FKSDB\Components\Controls\ColumnPrinter\ColumnRendererComponent;
use FKSDB\Components\Grids\ListComponent\ItemComponent;
use Fykosak\NetteORM\Model;
use Nette\DI\Container;

class TemplateItem extends ItemComponent
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

    protected function createComponentPrinter(): ColumnRendererComponent
    {
        return new ColumnRendererComponent($this->getContext());
    }

    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'template.latte';
    }
}
