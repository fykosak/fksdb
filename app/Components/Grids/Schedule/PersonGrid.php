<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Schedule;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use Fykosak\Utils\UI\Title;
use Nette\Application\UI\Presenter;

class PersonGrid extends BaseGrid
{
    protected function setData(): void
    {
    }

    /**
     * @throws \InvalidArgumentException
     */
    final public function render(?PersonModel $person = null, ?EventModel $event = null): void
    {
        if (!$event || !$person) {
            throw new \InvalidArgumentException();
        }
        $this->data = $person->getScheduleForEvent($event);
        parent::render();
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(Presenter $presenter): void
    {
        parent::configure($presenter);
        $this->paginate = false;

        $this->addColumn(
            'person_schedule_id',
            new Title(null, _('#')),
            fn(PersonScheduleModel $model) => $model->person_schedule_id
        );
        $this->addColumns([
            'schedule_group.name',
            'schedule_item.name',
            'schedule_item.price_czk',
            'schedule_item.price_eur',
            'payment.payment',
        ]);
    }

    protected function getData(): void
    {
    }
}
