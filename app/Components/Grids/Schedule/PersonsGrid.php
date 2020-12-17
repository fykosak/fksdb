<?php

namespace FKSDB\Components\Grids\Schedule;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Model\Exceptions\BadTypeException;
use FKSDB\Model\ORM\Models\Schedule\ModelPersonSchedule;
use FKSDB\Model\ORM\Models\Schedule\ModelScheduleItem;
use Nette\Application\IPresenter;
use Nette\DI\Container;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateColumnException;

/**
 * Class PersonsGrid
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PersonsGrid extends BaseGrid {

    private ModelScheduleItem $item;

    public function __construct(Container $container, ModelScheduleItem $item) {
        parent::__construct($container);
        $this->item = $item;
    }

    protected function getData(): IDataSource {
        return new NDataSource($this->item->getInterested());
    }

    /**
     * @param IPresenter $presenter
     * @return void
     * @throws BadTypeException
     * @throws DuplicateColumnException
     */
    protected function configure(IPresenter $presenter): void {
        parent::configure($presenter);
        $this->paginate = false;
        $this->addColumn('person_schedule_id', _('#'));
        $this->addColumns(['person.full_name', 'event.role', 'payment.payment']);
    }

    protected function getModelClassName(): string {
        return ModelPersonSchedule::class;
    }
}
