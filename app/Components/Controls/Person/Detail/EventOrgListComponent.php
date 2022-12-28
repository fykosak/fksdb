<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Person\Detail;

use FKSDB\Components\Grids\ListComponent\Button\PresenterButton;
use FKSDB\Components\Grids\ListComponent\Container\RowContainer;
use FKSDB\Components\Grids\ListComponent\Referenced\TemplateItem;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\Models\EventOrgModel;
use Fykosak\Utils\UI\Title;

class EventOrgListComponent extends BaseListComponent
{

    protected function getMinimalPermissions(): int
    {
        return FieldLevelPermission::ALLOW_RESTRICT;
    }

    protected function getTitle(): Title
    {
        return new Title(null, _('Event organisers'));
    }

    protected function getModels(): iterable
    {
        return $this->person->getEventOrgs();
    }

    protected function configure(): void
    {
        $this->classNameCallback = fn(EventOrgModel $eventOrg) => 'alert alert-' .
            ($eventOrg->event->event_type->getSymbol() !== 'secondary' ? $eventOrg->event->event_type->getSymbol()
                : $eventOrg->event->event_type->contest->getContestSymbol());

        $row0 = new RowContainer($this->container);
        $this->addComponent($row0, 'row0');
        $row0->addComponent(new TemplateItem($this->container, '@event.name'), 'event__name');
        $row0->addComponent(new TemplateItem($this->container, '@event.event_type'), 'event__type');
        $row1 = new RowContainer($this->container);
        $this->addComponent($row1, 'row1');
        $row1->addComponent(new TemplateItem($this->container, '@event_org.note'), 'event_org_note');
        $this->addButton(
            new PresenterButton($this->container, new Title(null, _('Edit')), fn(EventOrgModel $eventOrg) => [
                ':Event:EventOrg:edit',
                [
                    'eventId' => $eventOrg->event_id,
                    'id' => $eventOrg->e_org_id,
                ],
            ]),
            'edit'
        );
        $this->addButton(
            new PresenterButton($this->container, new Title(null, _('Detail')), fn(EventOrgModel $eventOrg) => [
                ':Event:EventOrg:detail',
                [
                    'eventId' => $eventOrg->event_id,
                    'id' => $eventOrg->e_org_id,
                ],
            ]),
            'detail'
        );
    }
}
