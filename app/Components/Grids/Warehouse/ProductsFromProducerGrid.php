<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Warehouse;

use FKSDB\Components\Grids\EntityGrid;
use FKSDB\Models\ORM\Models\Warehouse\ProducerModel;
use FKSDB\Models\ORM\Services\Warehouse\ProductService;
use Nette\DI\Container;

class ProductsFromProducerGrid extends EntityGrid
{
    public function __construct(Container $container, ProducerModel $producer)
    {
        parent::__construct($container, ProductService::class, [
            'warehouse_product.product_id',
            'warehouse_product.name_cs',
            'warehouse_product.name_en',
            'warehouse_product.category',
        ], [
            'producer_id' => $producer->producer_id,
        ]);
    }
}
