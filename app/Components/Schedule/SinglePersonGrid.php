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
use Nette\DI\Container;

/**
 * @phpstan-extends BaseGrid<PersonScheduleModel,array{}>
 */
final class SinglePersonGrid extends BaseGrid
{
    private EventModel $event;
    private PersonModel $person;

    public function __construct(Container $container, PersonModel $person, EventModel $event)
    {
        parent::__construct($container);
        $this->event = $event;
        $this->person = $person;
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
