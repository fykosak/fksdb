<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Person\Detail;

use FKSDB\Models\ORM\FieldLevelPermissionValue;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\Models\EventOrgModel;
use Fykosak\Utils\UI\Title;

class EventOrgListComponent extends BaseListComponent
{

    protected function getMinimalPermissions(): FieldLevelPermissionValue
    {
        return FieldLevelPermissionValue::Restrict;
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

        $row0 = $this->createColumnsRow('row0');
        $row0->createReferencedColumn('event.name')->className .= ' fw-bold';
        $row0->createReferencedColumn('event.event_type')->className .= ' text-muted';
        $row1 = $this->createColumnsRow('row1');
        $row1->createReferencedColumn('event_org.note');
        $this->createDefaultButton('edit', _('Edit'), fn(EventOrgModel $eventOrg) => [
            ':Event:EventOrg:edit',
            [
                'eventId' => $eventOrg->event_id,
                'id' => $eventOrg->e_org_id,
            ],
        ]);
        $this->createDefaultButton('detail', _('Detail'), fn(EventOrgModel $eventOrg) => [
            ':Event:EventOrg:detail',
            [
                'eventId' => $eventOrg->event_id,
                'id' => $eventOrg->e_org_id,
            ],
        ]);
    }
}
