<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Warehouse;

use FKSDB\Components\Grids\EntityGrid;
use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Services\Warehouse\ItemService;
use FKSDB\Models\Exceptions\BadTypeException;
use Nette\DI\Container;
use Nette\Application\UI\Presenter;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;

class ItemsGrid extends EntityGrid
{

    public function __construct(Container $container, ContestModel $contest)
    {
        parent::__construct($container, ItemService::class, [
            'warehouse_item.item_id',
            'warehouse_product.name_cs',
            'warehouse_item.state',
            'warehouse_item.description_cs',
            'warehouse_item.data',
            'warehouse_item.purchase_price',
            'warehouse_item.purchase_currency'
        ], [
            'contest_id' => $contest->contest_id,
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
        $this->addLinkButton(':Warehouse:Item:edit', 'edit', _('Edit'), false, ['id' => 'item_id']);
    }
}
