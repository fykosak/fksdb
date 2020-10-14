<?php

namespace FKSDB\Components\Grids\Warehouse;

use FKSDB\Components\Grids\EntityGrid;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\Services\Warehouse\ServiceProducer;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use NiftyGrid\DuplicateColumnException;

class ProducersGrid extends EntityGrid {
    public function __construct(Container $container) {
        parent::__construct($container, ServiceProducer::class, [], []);
    }

    /**
     * @param Presenter $presenter
     * @return void
     * @throws DuplicateColumnException
     * @throws BadTypeException
     */
    protected function configure(Presenter $presenter): void {
        parent::configure($presenter);
        $this->setDefaultOrder('name');
        $this->addColumn('producer_id', _('Producer Id'));
        $this->addColumn('name', _('Name'));
    }
}
