<?php

declare(strict_types=1);

namespace FKSDB\Components\Schedule;

use FKSDB\Components\Grids\Components\BaseGrid;
use FKSDB\Components\Grids\Components\Renderer\RendererItem;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use Fykosak\NetteORM\TypedGroupedSelection;
use Fykosak\Utils\UI\Title;

/**
 * @phpstan-extends BaseGrid<PersonScheduleModel,array{}>
 */
final class PersonScheduleGrid extends BaseGrid
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

    /**
     * @phpstan-return TypedGroupedSelection<PersonScheduleModel>
     */
    protected function getModels(): TypedGroupedSelection
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
        $this->counter = false;

        $this->addTableColumn(
            new RendererItem(
                $this->container,
                fn(PersonScheduleModel $model) => (string)$model->person_schedule_id,
                new Title(null, _('Person schedule Id'))
            ),
            'person_schedule_id'
        );
        $this->addSimpleReferencedColumns([
            '@schedule_group.name',
            '@schedule_item.name',
            '@schedule_item.price_czk',
            '@schedule_item.price_eur',
            '@payment.payment',
        ]);
    }
}
