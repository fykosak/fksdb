<?php

namespace FKSDB\Components\Grids\Schedule;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Exceptions\NotImplementedException;
use FKSDB\ORM\Models\Schedule\ModelPersonSchedule;
use FKSDB\ORM\Models\Schedule\ModelScheduleItem;
use FKSDB\YearCalculator;
use Nette\Application\UI\Presenter;
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

        $this->addColumn('person', _('Person'))->setRenderer(function ($row) {
            $model = ModelPersonSchedule::createFromActiveRow($row);
            return $model->getPerson()->getFullName();
        })->setSortable(false);

        $this->addColumns(['event.role','payment.payment']);

        $this->addColumn('state', _('State'))->setRenderer(function ($row) {
            $model = ModelPersonSchedule::createFromActiveRow($row);
            return $model->state;
        });
    }

    protected function getModelClassName(): string {
        return ModelPersonSchedule::class;
    }
}
