<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components\Referenced;

use FKSDB\Components\Controls\ColumnPrinter\ColumnRendererComponent;
use FKSDB\Components\Grids\Components\BaseItem;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\FieldLevelPermissionValue;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

class TemplateItem extends BaseItem
{
    protected string $templateString;
    protected ?string $titleString;

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    public function __construct(Container $container, string $templateString, ?string $titleString = null)
    {
        $printer = new ColumnRendererComponent($container);
        parent::__construct(
            $container,
            $titleString
                ? new Title(null, $printer->renderToString($titleString, null, null))
                : new Title(null, '')
        );
        $this->templateString = $templateString;
        $this->titleString = $titleString;
    }

    public function render(?Model $model, ?FieldLevelPermissionValue $userPermission): void
    {
        $this->template->templateString = $this->templateString;
        $this->template->titleString = $this->titleString;
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
