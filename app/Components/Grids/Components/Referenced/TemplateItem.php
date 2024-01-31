<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components\Referenced;

use FKSDB\Components\Controls\ColumnPrinter\ColumnRendererComponent;
use FKSDB\Components\Grids\Components\BaseItem;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\FieldLevelPermissionValue;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

/**
 * @phpstan-template TModel of \Fykosak\NetteORM\Model\Model
 * @phpstan-template TModelHelper of \Fykosak\NetteORM\Model\Model
 * @phpstan-extends BaseItem<TModel>
 */
class TemplateItem extends BaseItem
{
    protected string $templateString;
    protected ?string $titleString;
    /** @phpstan-var (callable(TModel):TModelHelper)|null */
    protected $modelAccessorHelper = null;
    public ?Title $title;

    /**
     * @phpstan-param (callable(TModel):TModelHelper)|null $modelAccessorHelper
     */
    public function __construct(
        Container $container,
        string $templateString,
        ?string $titleString = null,
        ?callable $modelAccessorHelper = null
    ) {
        parent::__construct($container);
        $this->titleString = $titleString;
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
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.latte', [
            'model' => $model,
            'userPermission' => $userPermission,
            'templateString' => $this->templateString,
        ]);
    }

    protected function createComponentPrinter(): ColumnRendererComponent
    {
        return new ColumnRendererComponent($this->getContext());
    }

    public function renderTitle(): void
    {
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'title.latte', [
            'titleString' => $this->titleString,
        ]);
    }
}
