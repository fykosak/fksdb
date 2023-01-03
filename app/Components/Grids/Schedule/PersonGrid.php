<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Schedule;

use FKSDB\Components\Grids\Components\Grid;
use FKSDB\Components\Grids\Components\Renderer\RendererItem;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use Fykosak\Utils\UI\Title;
use Nette\Database\Table\Selection;

class PersonGrid extends Grid
{
    private EventModel $event;
    private PersonModel $person;

    /**
     * @throws \InvalidArgumentException
     */
    final public function render(?PersonModel $person = null, ?EventModel $event = null): void
    {
        $this->event = $event;
        $this->person = $person;
        parent::render();
    }

    protected function getModels(): Selection
    {
        return $this->person->getScheduleForEvent($this->event);
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(): void
    {
        $this->paginate = false;

        $this->addColumn(
            new RendererItem(
                $this->container,
                fn(PersonScheduleModel $model) => $model->person_schedule_id,
                new Title(null, _('#'))
            ),
            'person_schedule_id'
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
