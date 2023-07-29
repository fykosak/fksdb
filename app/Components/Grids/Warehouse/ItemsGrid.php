<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Warehouse;

use FKSDB\Components\Grids\EntityGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\Warehouse\ItemModel;
use FKSDB\Models\ORM\Services\Warehouse\ItemService;
use Nette\DI\Container;

/**
 * @phpstan-extends EntityGrid<ItemModel>
 */
class ItemsGrid extends EntityGrid
{
    public function __construct(Container $container, ContestModel $contest)
    {
        parent::__construct($container, ItemService::class, [
            'warehouse_item.item_id',
            'warehouse_product.name_cs',
            'warehouse_item.state',
            'warehouse_item.description_cs',
            'warehouse_item.data',
            'warehouse_item.purchase_price',
            'warehouse_item.purchase_currency',
        ], [
            'contest_id' => $contest->contest_id,
        ]);
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(): void
    {
        parent::configure();
        $this->addPresenterButton(':Warehouse:Item:edit', 'edit', _('Edit'), false, ['id' => 'item_id']);
    }
}
