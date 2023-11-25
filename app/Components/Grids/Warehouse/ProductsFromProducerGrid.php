<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Warehouse;

use FKSDB\Components\Grids\Components\BaseGrid;
use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\Warehouse\ProducerModel;
use FKSDB\Models\ORM\Models\Warehouse\ProductModel;
use Fykosak\NetteORM\Selection\TypedGroupedSelection;
use Nette\DI\Container;

/**
 * @phpstan-extends BaseGrid<ProductModel,array{}>
 */
class ProductsFromProducerGrid extends BaseGrid
{
    private ProducerModel $producer;

    public function __construct(Container $container, ProducerModel $producer)
    {
        parent::__construct($container);
        $this->producer = $producer;
    }

    /**
     * @phpstan-return TypedGroupedSelection<ProductModel>
     */
    protected function getModels(): TypedGroupedSelection
    {
        return $this->producer->related(DbNames::TAB_WAREHOUSE_PRODUCT, 'producer_id'); // @phpstan-ignore-line
    }

    protected function configure(): void
    {
        $this->addSimpleReferencedColumns([
            '@warehouse_product.product_id',
            '@warehouse_product.name_cs',
            '@warehouse_product.name_en',
            '@warehouse_product.category',
        ]);
    }
}
