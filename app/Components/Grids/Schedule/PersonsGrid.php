<?php

namespace FKSDB\Components\Grids\Schedule;

use FKSDB\Components\DatabaseReflection\ValuePrinters\EventRole;
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
     * @param YearCalculator $yearCalculator
     * @return void
     */
    public function injectYearCalculator(YearCalculator $yearCalculator) {
        $this->yearCalculator = $yearCalculator;
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
     * @return void
     * @throws DuplicateColumnException
     * @throws NotImplementedException
     */
    protected function configure($presenter) {
        parent::configure($presenter);
        $this->paginate = false;

        $this->addColumn('person_schedule_id', _('#'));

        $this->addColumns(['person.full_name']);

        $this->addColumnRole();

        $this->addColumns(['payment.payment']);

        $this->addColumn('state', _('State'))->setRenderer(function ($row) {
            $model = ModelPersonSchedule::createFromActiveRow($row);
            return $model->state;
        });
    }

    protected function getModelClassName(): string {
        return ModelPersonSchedule::class;
    }

    /**
     * @throws DuplicateColumnException
     */
    protected function addColumnRole() {
        $this->addColumn('role', _('Role'))
            ->setRenderer(function ($row) {
                $model = ModelPersonSchedule::createFromActiveRow($row);
                return EventRole::calculateRoles($model->getPerson(), $model->getScheduleItem()->getScheduleGroup()->getEvent(), $this->yearCalculator);
            })->setSortable(false);
    }
}
