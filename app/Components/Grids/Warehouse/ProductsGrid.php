<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Warehouse;

use FKSDB\Components\Grids\EntityGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Services\Warehouse\ProductService;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;

class ProductsGrid extends EntityGrid
{

    public function __construct(Container $container)
    {
        parent::__construct($container, ProductService::class, [
            'warehouse_product.product_id',
            'warehouse_product.name_cs',
            'warehouse_product.name_en',
            'warehouse_product.category',
            'warehouse_producer.name',
        ]);
    }

    /**
     * @param Presenter $presenter
     * @return void
     * @throws DuplicateColumnException
     * @throws BadTypeException
     * @throws DuplicateButtonException
     */
    protected function configure(Presenter $presenter): void
    {
        parent::configure($presenter);
        $this->addLinkButton(':Warehouse:Product:edit', 'edit', _('Edit'), false, ['id' => 'product_id']);
    }
}
