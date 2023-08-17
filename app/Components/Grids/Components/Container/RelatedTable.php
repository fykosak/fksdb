<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components\Container;

use FKSDB\Components\Grids\Components\BaseItem;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

/**
 * @phpstan-template TModel of \Fykosak\NetteORM\Model
 * @phpstan-template TRelatedModel of \Fykosak\NetteORM\Model
 * @phpstan-extends BaseItem<TModel>
 */
class RelatedTable extends BaseItem
{
    /** @phpstan-var callable(TModel):iterable<TRelatedModel> */
    private $modelToIterator;
    private bool $head;
    /** @phpstan-var TableRow<TRelatedModel> */
    public TableRow $tableRow;

    /**
     * @phpstan-param callable(TModel):iterable<TRelatedModel> $modelToIterator
     */
    public function __construct(Container $container, callable $modelToIterator, Title $title, bool $head = false)
    {
        parent::__construct($container, $title);
        $this->modelToIterator = $modelToIterator;
        $this->head = $head;
        $this->tableRow = new TableRow($container, $title);
        $this->addComponent($this->tableRow, 'row');
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

    /**
     * @phpstan-param BaseItem<TRelatedModel> $component
     */
    public function addColumn(BaseItem $component, string $name): void
    {
        $this->tableRow->addComponent($component, $name);
    }

    /**
     * @phpstan-param BaseItem<TRelatedModel> $component
     */
    public function addButton(BaseItem $component, string $name): void
    {
        $this->tableRow->addButton($component, $name);
    }
}
