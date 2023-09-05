<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Person\Detail;

use FKSDB\Components\Grids\Components\Button\PresenterButton;
use FKSDB\Components\Grids\Components\Container\RowContainer;
use FKSDB\Components\Grids\Components\Referenced\TemplateItem;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\Models\EventOrganizerModel;
use Fykosak\NetteORM\TypedGroupedSelection;
use Fykosak\Utils\UI\Title;

/**
 * @phpstan-extends DetailComponent<EventOrganizerModel>
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
            ($model->event->event_type->getSymbol() !== 'secondary' ? $model->event->event_type->getSymbol()
                : $model->event->event_type->contest->getContestSymbol());
        /** @phpstan-var RowContainer<EventOrganizerModel> $row0 */
        $row0 = new RowContainer($this->container, new Title(null, ''));
        $this->addRow($row0, 'row0');
        $row0->addComponent(new TemplateItem($this->container, '@event.name', '@event.name:title'), 'event__name');
        $row0->addComponent(
            new TemplateItem($this->container, '@event.event_type', '@event.event_type:title'),
            'event__type'
        );
        /** @phpstan-var RowContainer<EventOrganizerModel> $row1 */
        $row1 = new RowContainer($this->container, new Title(null, ''));
        $this->addRow($row1, 'row1');
        $row1->addComponent(
            new TemplateItem($this->container, '@event_org.note', '@event_org.note:title'),
            'event_org_note'
        );
        $this->addButton(
            new PresenterButton(
                $this->container,
                null,
                new Title(null, _('Edit')),
                fn(EventOrganizerModel $model) => [
                    ':Event:EventOrg:edit',
                    [
                        'eventId' => $model->event_id,
                        'id' => $model->e_org_id,
                    ],
                ]
            ),
            'edit'
        );
        $this->addButton(
            new PresenterButton(
                $this->container,
                null,
                new Title(null, _('Detail')),
                fn(EventOrganizerModel $model) => [
                    ':Event:EventOrg:detail',
                    [
                        'eventId' => $model->event_id,
                        'id' => $model->e_org_id,
                    ],
                ]
            ),
            'detail'
        );
    }
}
