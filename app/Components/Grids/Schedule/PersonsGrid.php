<?php

namespace FKSDB\Components\Grids\Schedule;

use FKSDB\Components\DatabaseReflection\ValuePrinters\EventRole;
use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use FKSDB\Components\Grids\BaseGrid;
use FKSDB\ORM\Models\Schedule\ModelPersonSchedule;
use FKSDB\ORM\Models\Schedule\ModelScheduleItem;
use FKSDB\YearCalculator;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateColumnException;

/**
 * Class PersonsGrid
 * @package FKSDB\Components\Grids\Schedule
 */
class PersonsGrid extends BaseGrid {
    /**
     * @var YearCalculator
     */
    private $yearCalculator;

    /**
     * PersonsGrid constructor.
     * @param TableReflectionFactory $tableReflectionFactory
     * @param YearCalculator $yearCalculator
     */
    public function __construct(TableReflectionFactory $tableReflectionFactory, YearCalculator $yearCalculator) {
        $this->yearCalculator = $yearCalculator;
        parent::__construct($tableReflectionFactory);
    }

    /**
     * @var ModelScheduleItem
     */
    private $item;

    /**
     * @param ModelScheduleItem $item
     */
    public function setItem(ModelScheduleItem $item) {
        $this->item = $item;
        $persons = $this->item->getInterested();
        $dataSource = new NDataSource($persons);
        $this->setDataSource($dataSource);
    }

    /**
     * @param $presenter
     * @throws DuplicateColumnException
     */
    protected function configure($presenter) {
        parent::configure($presenter);
        $this->paginate = false;

        $this->addColumn('person_schedule_id', _('#'));

        $this->addColumn('person', _('Person'))->setRenderer(function ($row) {
            $model = ModelPersonSchedule::createFromActiveRow($row);
            return $model->getPerson()->getFullName();
        })->setSortable(false);

        $this->addColumnRole();

        $this->addColumns(['referenced.payment_id']);

        $this->addColumn('state', _('State'))->setRenderer(function ($row) {
            $model = ModelPersonSchedule::createFromActiveRow($row);
            return $model->state;
        });
    }

    /**
     * @return string
     */
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
