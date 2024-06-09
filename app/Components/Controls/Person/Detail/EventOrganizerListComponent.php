<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Person\Detail;

use FKSDB\Components\Grids\Components\Referenced\SimpleItem;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\Models\EventOrganizerModel;
use Fykosak\NetteORM\Selection\TypedGroupedSelection;
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

    protected function configure(): void
    {
        $this->classNameCallback = fn(EventOrganizerModel $model): string => $model->event->event_type->getSymbol();
        $row0 = $this->createRow();
        $row0->addComponent(new SimpleItem($this->container, '@event.name_new'), 'event__name');
        $row0->addComponent(
            new SimpleItem($this->container, '@event_type.name'),
            'event__type'
        );
        $row1 = $this->createRow();
        $row1->addComponent(
            new SimpleItem($this->container, '@event_org.note'),
            'event_org_note'
        );
        $this->addPresenterButton(':Event:EventOrganizer:edit', 'edit', new Title(null, _('button.edit')), false, [
            'eventId' => 'event_id',
            'id' => 'e_org_id',
        ]);
        $this->addPresenterButton(
            ':Event:EventOrganizer:detail',
            'detail',
            new Title(null, _('button.detail')),
            false,
            [
                'eventId' => 'event_id',
                'id' => 'e_org_id',
            ]
        );
    }
}
