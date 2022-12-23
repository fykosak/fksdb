<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Warehouse;

use FKSDB\Components\Grids\EntityGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Services\Warehouse\ProducerService;
use Nette\Application\UI\Presenter;
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
     */
    protected function configure(Presenter $presenter): void
    {
        parent::configure($presenter);
        $this->setDefaultOrder('name');
    }
}
