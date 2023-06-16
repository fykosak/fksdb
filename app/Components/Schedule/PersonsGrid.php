<?php

declare(strict_types=1);

namespace FKSDB\Components\Schedule;

use FKSDB\Components\Grids\Components\Grid;
use FKSDB\Components\Grids\Components\Renderer\RendererBaseItem;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use Fykosak\NetteORM\TypedGroupedSelection;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

class PersonsGrid extends Grid
{
    private ScheduleItemModel $item;

    public function __construct(Container $container, ScheduleItemModel $item)
    {
        parent::__construct($container);
        $this->item = $item;
    }

    protected function getModels(): TypedGroupedSelection
    {
        return $this->item->getInterested();
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(): void
    {
        $this->paginate = false;
        $this->addColumn(
            new RendererBaseItem(
                $this->container,
                fn(PersonScheduleModel $model) => $model->person_schedule_id,
                new Title(null, _('#'))
            ),
            'person_schedule_id'
        );
        $this->addColumns(['person.full_name', 'event.role', 'payment.payment', 'person_schedule.state']);
    }
}
