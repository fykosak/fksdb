<?php

namespace FKSDB\Components\Grids\Warehouse;

use FKSDB\Components\Grids\EntityGrid;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\Models\Warehouse\ModelProducer;
use FKSDB\ORM\Services\Warehouse\ServiceProduct;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use NiftyGrid\DuplicateColumnException;

/**
 * Class ProductsFromProducerGrid
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ProductsFromProducerGrid extends EntityGrid {

    public function __construct(Container $container, ModelProducer $producer) {
        parent::__construct($container, ServiceProduct::class, [
            'warehouse_product.product_id',
            'warehouse_product.category',
        ], [
            'producer_id' => $producer->producer_id,
        ]);
    }

    /**
     * @param Presenter $presenter
     * @return void
     * @throws DuplicateColumnException
     * @throws BadTypeException
     */
    protected function configure(Presenter $presenter): void {
        parent::configure($presenter);
        $this->addColumn('name_cs', _('Name cs'));
        $this->addColumn('name_en', _('Name en'));
    }
}
