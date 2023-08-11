<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components\Container;

use FKSDB\Components\Grids\Components\BaseItem;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

/**
 * @template M of \Fykosak\NetteORM\Model
 * @template C of \Fykosak\NetteORM\Model
 * @phpstan-extends BaseItem<M>
 */
class RelatedTable extends BaseItem
{
    /** @var callable(M):iterable<C> */
    private $modelToIterator;
    private bool $head;
    /** @var TableRow<C> */
    public TableRow $tableRow;

    /**
     * @phpstan-param callable(M):iterable<C> $modelToIterator
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
     * @param M|null $model
     */
    public function render(?Model $model, ?int $userPermission): void
    {
        $this->doRender($model, $userPermission, ['models' => ($this->modelToIterator)($model), 'head' => $this->head]);
    }

    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'relatedTable.latte';
    }

    /**
     * @phpstan-param BaseItem<C> $component
     */
    public function addColumn(BaseItem $component, string $name): void
    {
        $this->tableRow->addComponent($component, $name);
    }

    /**
     * @phpstan-param BaseItem<C> $component
     */
    public function addButton(BaseItem $component, string $name): void
    {
        $this->tableRow->addButton($component, $name);
    }
}
