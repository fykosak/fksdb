<?php

namespace FKSDB\Components\Grids\Schedule;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Exceptions\NotImplementedException;
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
    /** @var ModelScheduleItem */
    private $item;

    /**
     * PersonsGrid constructor.
     * @param Container $container
     * @param ModelScheduleItem $item
     */
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
     * @throws NotImplementedException
     */
    protected function configure(Presenter $presenter) {
        parent::configure($presenter);
        $this->paginate = false;

        $this->addColumn('person_schedule_id', _('#'));

        $this->addColumns(['person.full_name', 'event.role', 'payment.payment']);

        $this->addColumn('state', _('State'))->setRenderer(function ($row) {
            $model = ModelPersonSchedule::createFromActiveRow($row);
            return $model->state;
        });
    }

    protected function getModelClassName(): string {
        return ModelPersonSchedule::class;
    }
}
