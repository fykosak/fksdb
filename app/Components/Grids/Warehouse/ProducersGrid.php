<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Warehouse;

use FKSDB\Components\Grids\Components\BaseGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\Warehouse\ProducerModel;
use FKSDB\Models\ORM\Services\Warehouse\ProducerService;
use Fykosak\NetteORM\Selection\TypedSelection;

/**
 * @phpstan-extends BaseGrid<ProducerModel,array{}>
 */
class ProducersGrid extends BaseGrid
{
    private ProducerService $service;

    public function inject(ProducerService $service): void
    {
        $this->service = $service;
    }

    /**
     * @phpstan-return TypedSelection<ProducerModel>
     */
    protected function getModels(): TypedSelection
    {
        return $this->service->getTable()->order('name');
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(): void
    {
        $this->addSimpleReferencedColumns([
            '@warehouse_producer.producer_id',
            '@warehouse_producer.name',
        ]);
    }
}
