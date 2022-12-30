<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Warehouse;

use FKSDB\Components\Grids\EntityGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Services\Warehouse\ProducerService;
use Nette\DI\Container;

class ProducersGrid extends EntityGrid
{
    public function __construct(Container $container)
    {
        parent::__construct($container, ProducerService::class, [
            'warehouse_producer.producer_id',
            'warehouse_producer.name',
        ]);
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(): void
    {
        parent::configure();
        $this->data->order('name');
    }
}
