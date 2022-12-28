<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Schedule;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use Fykosak\NetteORM\TypedGroupedSelection;
use Fykosak\Utils\UI\Title;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;

class PersonsGrid extends BaseGrid
{

    private ScheduleItemModel $item;

    public function __construct(Container $container, ScheduleItemModel $item)
    {
        parent::__construct($container);
        $this->item = $item;
    }

    protected function getData(): TypedGroupedSelection
    {
        return $this->item->getInterested();
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
        $this->addColumns(['person.full_name', 'event.role', 'payment.payment']);
    }
}
