<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components\Referenced;

use FKSDB\Components\Controls\ColumnPrinter\ColumnRendererComponent;
use FKSDB\Components\Grids\Components\BaseItem;
use FKSDB\Models\Exceptions\BadTypeException;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

/**
 * @template TModel of \Fykosak\NetteORM\Model
 * @template TModelHelper of \Fykosak\NetteORM\Model
 * @phpstan-extends BaseItem<TModel>
 */
class TemplateItem extends BaseItem
{
    protected string $templateString;
    protected ?string $titleString;
    /** @var (callable(TModel):TModelHelper)|null */
    protected $modelAccessorHelper = null;
    protected ColumnRendererComponent $printer;

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     * @phpstan-param (callable(TModel):TModelHelper)|null $modelAccessorHelper
     */
    public function __construct(
        Container $container,
        string $templateString,
        ?string $titleString = null,
        ?callable $modelAccessorHelper = null
    ) {
        $this->printer = new ColumnRendererComponent($container);
        parent::__construct(
            $container,
            $titleString
                ? new Title(null, $this->printer->renderToString($titleString, null, null))
                : new Title(null, '')
        );
        $this->templateString = $templateString;
        $this->modelAccessorHelper = $modelAccessorHelper;
    }

    /**
     * @phpstan-param TModel $model
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    public function render(Model $model, int $userPermission): void
    {
        $model = isset($this->modelAccessorHelper) ? ($this->modelAccessorHelper)($model) : $model;
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'template.latte', [
            'title' => $this->title,
            'html' => $this->printer->renderToString($this->templateString, $model, $userPermission),
        ]);
    }

    protected function createComponentPrinter(): ColumnRendererComponent
    {
        return new ColumnRendererComponent($this->getContext());
    }
}
