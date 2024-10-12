<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Warehouse;

use FKSDB\Components\Grids\Components\BaseGrid;
use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\Warehouse\WarehouseItemVariantModel;
use Fykosak\NetteORM\Selection\TypedGroupedSelection;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

/**
 * @phpstan-extends BaseGrid<WarehouseItemVariantModel,array{}>
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
     * @phpstan-return TypedGroupedSelection<WarehouseItemVariantModel>
     */
    protected function getModels(): TypedGroupedSelection
    {
        return $this->contest->related(DbNames::TAB_WAREHOUSE_ITEM); // @phpstan-ignore-line
    }

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
        $this->addPresenterButton(
            ':Warehouse:Item:edit',
            'edit',
            new Title(null, _('button.edit')),
            false,
            ['id' => 'item_id']
        );
    }
}
