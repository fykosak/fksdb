<?php

namespace FKSDB\Components\Grids\Schedule;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\Models\Schedule\ModelPersonSchedule;
use FKSDB\ORM\Models\Schedule\ModelScheduleItem;
use Nette\Application\UI\Presenter;
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
     * @param Presenter $presenter
     * @return void
     * @throws DuplicateColumnException
     * @throws BadTypeException
     */
    protected function configure(Presenter $presenter): void {
        parent::configure($presenter);
        $this->paginate = false;
        $this->addColumn('person_schedule_id', _('#'));
        $this->addColumns(['person.full_name', 'event.role', 'payment.payment']);
    }

    protected function getModelClassName(): string {
        return ModelPersonSchedule::class;
    }
}
