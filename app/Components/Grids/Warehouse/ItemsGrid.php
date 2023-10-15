<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Warehouse;

use FKSDB\Components\Grids\Components\BaseGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\Warehouse\ItemModel;
use Fykosak\NetteORM\TypedGroupedSelection;
use Nette\DI\Container;

/**
 * @phpstan-extends BaseGrid<ItemModel,array{}>
 */
class ItemsGrid extends BaseGrid
{
    private ContestModel $contest;

    public function __construct(Container $container, ContestModel $contest)
    {
        parent::__construct($container);
        $this->contest = $contest;
    }

    /**
     * @phpstan-return TypedGroupedSelection<ItemModel>
     */
    protected function getModels(): TypedGroupedSelection
    {
        return $this->contest->related(DbNames::TAB_WAREHOUSE_ITEM); // @phpstan-ignore-line
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(): void
    {
        $this->addSimpleReferencedColumns([
            '@warehouse_item.item_id',
            '@warehouse_product.name_cs',
            '@warehouse_item.state',
            '@warehouse_item.description_cs',
            '@warehouse_item.data',
            '@warehouse_item.purchase_price',
            '@warehouse_item.purchase_currency',
        ]);
        $this->addPresenterButton(':Warehouse:Item:edit', 'edit', _('button.edit'), false, ['id' => 'item_id']);
    }
}
