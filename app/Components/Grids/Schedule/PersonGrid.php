<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Schedule;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\PersonModel;
use Nette\Application\UI\Presenter;
use NiftyGrid\DataSource\NDataSource;

class PersonGrid extends BaseGrid
{

    public function setData(EventModel $event, PersonModel $person): void
    {
        $query = $person->getScheduleForEvent($event);
        $dataSource = new NDataSource($query);
        $this->setDataSource($dataSource);
    }

    /**
     * @throws \InvalidArgumentException
     */
    final public function render(?PersonModel $person = null, ?EventModel $event = null): void
    {
        if (!$event || !$person) {
            throw new \InvalidArgumentException();
        }
        $this->setData($event, $person);
        parent::render();
    }

    /**
     * @throws BadTypeException
     */
    protected function configure(Presenter $presenter): void
    {
        parent::configure($presenter);
        $this->paginate = false;

        $this->addColumn('person_schedule_id', _('#'));
        $this->addColumns([
            'schedule_group.name',
            'schedule_item.name',
            'schedule_item.price_czk',
            'schedule_item.price_eur',
            'payment.payment',
        ]);
    }
}
