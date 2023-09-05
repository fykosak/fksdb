<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\EventRole;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventOrganizerModel;
use Nette\Utils\Html;

class EventOrganizerRole extends EventRole
{
    public EventOrganizerModel $eventOrganizer;

    public function __construct(EventModel $event, EventOrganizerModel $eventOrganizer)
    {
        parent::__construct('event.org', $event);
        $this->eventOrganizer = $eventOrganizer;
    }

    public function badge(): Html
    {
        return Html::el('span')
            ->addAttributes(['class' => 'badge bg-color-7'])
            ->addText(sprintf(_('Event organizer: %s'), $this->eventOrganizer->note));
    }
}
