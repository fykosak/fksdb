<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components\Table;

use FKSDB\Components\Grids\Components\BaseItem;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

/**
 * @phpstan-template TModel of Model
 * @phpstan-template TRelatedModel of Model
 * @phpstan-extends BaseItem<TModel>
 */
class RelatedTable extends BaseItem
{
    /** @phpstan-use TableTrait<TRelatedModel> */
    use TableTrait;

    /** @phpstan-var callable(TModel):iterable<TRelatedModel> */
    private $modelToIterator;
    private bool $head;
    private Title $title;

    /**
     * @phpstan-param callable(TModel):iterable<TRelatedModel> $modelToIterator
     */
    public function __construct(Container $container, callable $modelToIterator, Title $title, bool $head = false)
    {
        parent::__construct($container);
        $this->title = $title;
        $this->modelToIterator = $modelToIterator;
        $this->head = $head;
        $this->registerTable($container);
    }

    /**
     * @phpstan-param TModel $model
     */
    public function render(Model $model, int $userPermission): void
    {
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'relatedTable.latte', [
            'models' => ($this->modelToIterator)($model),
            'head' => $this->head,
            'title' => $this->title,
            'userPermission' => $userPermission,
        ]);
    }

    public function renderTitle(): void
    {
        $this->template->render(
            __DIR__ . DIRECTORY_SEPARATOR . '../html.latte',
            [
                'html' => $this->title->toHtml(),
            ]
        );
    }
}
