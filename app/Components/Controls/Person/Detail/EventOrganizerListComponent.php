<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Person\Detail;

use FKSDB\Components\Grids\Components\Referenced\SimpleItem;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\Models\EventOrganizerModel;
use Fykosak\NetteORM\TypedGroupedSelection;
use Fykosak\Utils\UI\Title;

/**
 * @phpstan-extends DetailComponent<EventOrganizerModel,array{}>
 */
class EventOrganizerListComponent extends DetailComponent
{
    protected function getMinimalPermissions(): int
    {
        return FieldLevelPermission::ALLOW_RESTRICT;
    }

    protected function getHeadline(): Title
    {
        return new Title(null, _('Event organizers'));
    }

    /**
     * @phpstan-return TypedGroupedSelection<EventOrganizerModel>
     */
    protected function getModels(): TypedGroupedSelection
    {
        return $this->person->getEventOrganizers();
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(): void
    {
        $this->classNameCallback = fn(EventOrganizerModel $model) => 'alert alert-' .
            $model->event->event_type->getSymbol();
        $row0 = $this->createRow();
        $row0->addComponent(new SimpleItem($this->container, '@event.name'), 'event__name');
        $row0->addComponent(
            new SimpleItem($this->container, '@event.event_type'),
            'event__type'
        );
        $row1 = $this->createRow();
        $row1->addComponent(
            new SimpleItem($this->container, '@event_org.note'),
            'event_org_note'
        );
        $this->addPresenterButton(':Event:EventOrganizer:edit', 'edit', _('Edit'), false, [
            'eventId' => 'event_id',
            'id' => 'e_org_id',
        ]);
        $this->addPresenterButton(':Event:EventOrganizer:detail', 'detail', _('Detail'), false, [
            'eventId' => 'event_id',
            'id' => 'e_org_id',
        ]);
    }
}