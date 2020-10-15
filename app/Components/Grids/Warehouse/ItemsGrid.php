<?php

namespace FKSDB\Components\Grids\Warehouse;

use FKSDB\Components\Grids\EntityGrid;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Services\Warehouse\ServiceItem;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use NiftyGrid\DuplicateColumnException;

/**
 * Class ItemsGrid
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ItemsGrid extends EntityGrid {

    public function __construct(Container $container, ModelContest $contest) {
        parent::__construct($container, ServiceItem::class, [
            'contest.contest',
            ], [
            'contest_id' => $contest->contest_id,
        ]);
    }

    /**
     * @param Presenter $presenter
     * @return void
     * @throws BadTypeException
     * @throws DuplicateColumnException
     */
    protected function configure(Presenter $presenter): void {
        parent::configure($presenter);
    }
}
