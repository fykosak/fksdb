<?php

namespace FKSDB\Components\Grids\Schedule;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Exceptions\NotImplementedException;
use FKSDB\ORM\Models\Schedule\ModelPersonSchedule;
use FKSDB\ORM\Models\Schedule\ModelScheduleItem;
use FKSDB\YearCalculator;
use Nette\DI\Container;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateColumnException;

/**
 * Class PersonsGrid
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PersonsGrid extends BaseGrid {
    /**
     * @var YearCalculator
     */
    private $yearCalculator;

    /**
     * PersonsGrid constructor.
     * @param Container $container
     */
    public function __construct(Container $container) {
        $this->yearCalculator = $container->getByType(YearCalculator::class);
        parent::__construct($container);
    }

    /**
     * @param ModelScheduleItem $item
     * @return void
     */
    public function setItem(ModelScheduleItem $item) {
        $dataSource = new NDataSource($item->getInterested());
        $this->setDataSource($dataSource);
    }

    /**
     * @param $presenter
     * @throws DuplicateColumnException
     * @throws NotImplementedException
     */
    protected function configure($presenter) {
        parent::configure($presenter);
        $this->paginate = false;

        $this->addColumn('person_schedule_id', _('#'));

        $this->addColumn('person', _('Person'))->setRenderer(function ($row) {
            $model = ModelPersonSchedule::createFromActiveRow($row);
            return $model->getPerson()->getFullName();
        })->setSortable(false);

        $this->addColumns(['event.role']);

        $this->addColumns(['referenced.payment_id']);

        $this->addColumn('state', _('State'))->setRenderer(function ($row) {
            $model = ModelPersonSchedule::createFromActiveRow($row);
            return $model->state;
        });
    }

    protected function getModelClassName(): string {
        return ModelPersonSchedule::class;
    }
}
