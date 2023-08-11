<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Warehouse;

use FKSDB\Components\Grids\EntityGrid;
use FKSDB\Models\ORM\Models\Warehouse\ProducerModel;
use FKSDB\Models\ORM\Services\Warehouse\ProducerService;
use Fykosak\NetteORM\TypedSelection;
use Nette\DI\Container;

/**
 * @phpstan-extends EntityGrid<ProducerModel>
 */
class ProducersGrid extends EntityGrid
{
    public function __construct(Container $container)
    {
        parent::__construct($container, ProducerService::class, [
            'warehouse_producer.producer_id',
            'warehouse_producer.name',
        ]);
    }

    protected function getModels(): TypedSelection
    {
        $query = parent::getModels();
        $query->order('name');
        return $query;
    }
}
