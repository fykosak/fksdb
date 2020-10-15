<?php

namespace FKSDB\Components\Grids\Warehouse;

use FKSDB\Components\Grids\EntityGrid;
use FKSDB\ORM\Models\Warehouse\ModelProducer;
use FKSDB\ORM\Services\Warehouse\ServiceProduct;
use Nette\DI\Container;

/**
 * Class ProductsFromProducerGrid
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ProductsFromProducerGrid extends EntityGrid {

    public function __construct(Container $container, ModelProducer $producer) {
        parent::__construct($container, ServiceProduct::class, [
            'warehouse_product.product_id',
            'warehouse_product.name_cs',
            'warehouse_product.name_en',
            'warehouse_product.category',
        ], [
            'producer_id' => $producer->producer_id,
        ]);
    }
}
