<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Schedule;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

class PersonsGrid extends BaseGrid
{

    private ScheduleItemModel $item;

    public function __construct(Container $container, ScheduleItemModel $item)
    {
        parent::__construct($container);
        $this->item = $item;
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(): void
    {
        $this->data = $this->item->getInterested();
        $this->paginate = false;
        $this->addColumn(
            'person_schedule_id',
            new Title(null, _('#')),
            fn(PersonScheduleModel $model) => $model->person_schedule_id
        );
        $this->addColumns(['person.full_name', 'event.role', 'payment.payment']);
    }
}
