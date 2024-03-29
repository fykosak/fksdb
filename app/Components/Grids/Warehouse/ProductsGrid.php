<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Warehouse;

use FKSDB\Components\Grids\Components\BaseGrid;
use FKSDB\Models\ORM\Models\Warehouse\ProductModel;
use FKSDB\Models\ORM\Services\Warehouse\ProductService;
use Fykosak\NetteORM\Selection\TypedSelection;
use Fykosak\Utils\UI\Title;

/**
 * @phpstan-extends BaseGrid<ProductModel,array{}>
 */
class ProductsGrid extends BaseGrid
{
    private ProductService $service;

    public function inject(ProductService $service): void
    {
        $this->service = $service;
    }

    /**
     * @phpstan-return TypedSelection<ProductModel>
     */
    protected function getModels(): TypedSelection
    {
        return $this->service->getTable();
    }

    protected function configure(): void
    {
        $this->addSimpleReferencedColumns([
            '@warehouse_product.product_id',
            '@warehouse_product.name_cs',
            '@warehouse_product.name_en',
            '@warehouse_product.category',
            '@warehouse_producer.name',
        ]);
        $this->addPresenterButton(
            ':Warehouse:Product:edit',
            'edit',
            new Title(null, _('button.edit')),
            false,
            ['id' => 'product_id']
        );
    }
}
