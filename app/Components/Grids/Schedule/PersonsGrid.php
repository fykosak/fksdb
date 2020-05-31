<?php

namespace FKSDB\Components\Grids\Schedule;

use FKSDB\Components\DatabaseReflection\ValuePrinters\EventRole;
use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Exceptions\NotImplementedException;
use FKSDB\ORM\Models\Schedule\ModelPersonSchedule;
use FKSDB\ORM\Models\Schedule\ModelScheduleItem;
use FKSDB\YearCalculator;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateColumnException;

/**
 * Class PersonsGrid
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PersonsGrid extends BaseGrid {

    private YearCalculator $yearCalculator;

    public function injectYearCalculator(YearCalculator $yearCalculator): void {
        $this->yearCalculator = $yearCalculator;
    }

    public function setItem(ModelScheduleItem $item): void {
        $dataSource = new NDataSource($item->getInterested());
        $this->setDataSource($dataSource);
    }

    /**
     * @param $presenter
     * @return void
     * @throws DuplicateColumnException
     * @throws NotImplementedException
     * @throws BadTypeException
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
