<?php

namespace FKSDB\Components\Grids\Warehouse;

use FKSDB\Components\Grids\EntityGrid;
use FKSDB\Models\ORM\Models\ModelContest;
use FKSDB\Models\ORM\Services\Warehouse\ServiceItem;
use Nette\DI\Container;

class ItemsGrid extends EntityGrid {

    public function __construct(Container $container, ModelContest $contest) {
        parent::__construct($container, ServiceItem::class, [
            'contest.contest',
        ], [
            'contest_id' => $contest->contest_id,
        ]);
    }
}
