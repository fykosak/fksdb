<?php

namespace FKSDB\Components\Grids\Schedule;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Models\Schedule\ModelPersonSchedule;
use FKSDB\ORM\Models\Schedule\ModelScheduleItem;
use Nette\Application\UI\Presenter;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateColumnException;
use NiftyGrid\GridException;

/**
 * Class PersonGrid
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PersonGrid extends BaseGrid {

    /**
     * @param ModelEvent $event
     * @param ModelPerson $person
     * @return void
     */
    public function setData(ModelEvent $event, ModelPerson $person) {
        $query = $person->getScheduleForEvent($event);
        $dataSource = new NDataSource($query);
        $this->setDataSource($dataSource);
    }

    /**
     * @param ModelPerson|null $person
     * @param ModelEvent|null $event
     * @throws \InvalidArgumentException
     * @throws GridException
     */
    public function render(?ModelPerson $person = null, ?ModelEvent $event = null): void {
        if (!$event || !$person) {
            throw new \InvalidArgumentException();
        }
        $this->setData($event, $person);
        parent::render();
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
        $this->addColumns(['schedule_group.name', 'schedule_item.name', 'schedule_item.price_czk', 'schedule_item.price_eur', 'payment.payment']);
    }

    protected function getModelClassName(): string {
        return ModelPersonSchedule::class;
    }
}
