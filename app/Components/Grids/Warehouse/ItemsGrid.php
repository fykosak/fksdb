<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Warehouse;

use FKSDB\Components\Grids\EntityGrid;
use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Services\Warehouse\ItemService;
use Nette\DI\Container;

class ItemsGrid extends EntityGrid
{

    public function __construct(Container $container, ContestModel $contest)
    {
        parent::__construct($container, ItemService::class, [
            'contest.contest',
        ], [
            'contest_id' => $contest->contest_id,
        ]);
    }
}
