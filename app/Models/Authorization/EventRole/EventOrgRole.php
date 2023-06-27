<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\EventRole;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventOrgModel;
use Nette\Utils\Html;

class EventOrgRole extends EventRole
{
    public EventOrgModel $eventOrg;

    public function __construct(EventModel $event, EventOrgModel $eventOrg)
    {
        parent::__construct('event.org', $event);
        $this->eventOrg = $eventOrg;
    }

    public function badge(): Html
    {
        return Html::el('span')
            ->addAttributes(['class' => 'badge bg-color-7'])
            ->addText(_('Event org') . ($this->eventOrg->note ? (' - ' . $this->eventOrg->note) : ''));
    }
}
