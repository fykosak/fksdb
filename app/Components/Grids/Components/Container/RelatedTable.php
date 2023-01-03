<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components\Container;

use FKSDB\Components\Grids\Components\BaseItem;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

class RelatedTable extends BaseItem
{
    /** @var callable */
    private $modelToIterator;
    private bool $head;
    public TableRow $tableRow;

    public function __construct(Container $container, callable $callback, Title $title, bool $head = false)
    {
        parent::__construct($container, $title);
        $this->modelToIterator = $callback;
        $this->head = $head;
        $this->tableRow = new TableRow($container, $title);
        $this->addComponent($this->tableRow, 'row');
    }

    public function render(?Model $model, ?int $userPermission): void
    {
        $this->template->models = ($this->modelToIterator)($model);
        $this->template->head = $this->head;
        parent::render($model, $userPermission);
    }

    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'relatedTable.latte';
    }

    public function addColumn(BaseItem $component, string $name): void
    {
        $this->tableRow->addComponent($component, $name);
    }

    public function addButton(BaseItem $component, string $name): void
    {
        $this->tableRow->addButton($component, $name);
    }
}
