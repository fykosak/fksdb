<?php


namespace FKSDB\Components\Grids\Schedule;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\Models\Schedule\ModelScheduleGroup;
use FKSDB\ORM\Models\Schedule\ModelScheduleItem;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;

/**
 * Class ItemsGrid
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ItemsGrid extends BaseGrid {
    /**
     * @var ModelScheduleGroup
     */
    private $group;

    /**
     * ItemsGrid constructor.
     * @param Container $container
     * @param ModelScheduleGroup $group
     */
    public function __construct(Container $container, ModelScheduleGroup $group) {
        parent::__construct($container);
        $this->group = $group;
    }

    public function getModelClassName(): string {
        return ModelScheduleItem::class;
    }

    protected function getData(): IDataSource {
        $items = $this->group->getItems();
        return new NDataSource($items);
    }

    /**
     * @param Presenter $presenter
     * @return void
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     * @throws BadTypeException
     */
    protected function configure(Presenter $presenter) {
        parent::configure($presenter);
        $this->paginate = false;
        $this->addColumn('schedule_item_id', _('#'));
        $this->addColumns([
            'schedule_item.name_cs',
            'schedule_item.name_en',
            'schedule_item.price_czk',
            'schedule_item.price_eur',
            'schedule_item.capacity',
            'schedule_item.used_capacity',
            'schedule_item.require_id_number',
        ]);
        $this->addLinkButton('detail', 'detail', _('Detail'), true, ['id' => 'schedule_item_id']);
    }
}
