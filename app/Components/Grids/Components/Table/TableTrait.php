<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components\Table;

use FKSDB\Components\Grids\Components\BaseItem;
use Nette\DI\Container;

/**
 * @phpstan-template TModel of \Fykosak\NetteORM\Model
 */
trait TableTrait
{
    /** @phpstan-var Table<TModel> */
    protected Table $table;

    protected function registerTable(Container $container): void
    {
        $this->table = new Table($container);
        $this->addComponent($this->table, 'table');
    }

    /**
     * @phpstan-template TComponent of BaseItem<TModel>
     * @phpstan-param TComponent $component
     * @phpstan-return TComponent
     */
    public function addTableColumn(BaseItem $component, string $name): BaseItem
    {
        $this->table->addColumn($component, $name);// @phpstan-ignore-line
        return $component;
    }

    /**
     * @phpstan-template TComponent of BaseItem<TModel>
     * @phpstan-param TComponent $component
     * @phpstan-return TComponent
     */
    public function addTableButton(BaseItem $component, string $name): BaseItem
    {
        $this->table->addButton($component, $name);// @phpstan-ignore-line
        return $component;
    }
}
