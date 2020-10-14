<?php

namespace FKSDB\Components\Grids\Warehouse;

use FKSDB\Components\Grids\EntityGrid;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\Services\Warehouse\ServiceProduct;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use NiftyGrid\DuplicateColumnException;

/**
 * Class ProductsGrid
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ProductsGrid extends EntityGrid {

    public function __construct(Container $container) {
        parent::__construct($container, ServiceProduct::class, [
            'warehouse_product.product_id',
            'warehouse_product.category',
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
        $this->addColumn('producer_id', _('Producer Id'));
        $this->addColumn('name_cs', _('Name cs'));
        $this->addColumn('name_en', _('Name en'));
    }
}
