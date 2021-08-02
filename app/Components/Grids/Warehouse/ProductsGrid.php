<?php

namespace FKSDB\Components\Grids\Warehouse;

use FKSDB\Components\Grids\EntityGrid;
use FKSDB\Models\ORM\Services\Warehouse\ServiceProduct;
use Nette\DI\Container;

class ProductsGrid extends EntityGrid
{

    public function __construct(Container $container)
    {
        parent::__construct($container, ServiceProduct::class, [
            'warehouse_product.product_id',
            'warehouse_product.name_cs',
            'warehouse_product.name_en',
            'warehouse_product.category',
            'warehouse_producer.name',
        ]);
    }
}
