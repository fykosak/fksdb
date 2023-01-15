<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\EventRole;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use Nette\Utils\Html;

class ParticipantRole extends EventRole
{
    public EventParticipantModel $eventParticipant;

    public function __construct(EventModel $event, EventParticipantModel $eventParticipant)
    {
        parent::__construct('event.participant', $event);
        $this->eventParticipant = $eventParticipant;
    }

    public function badge(): Html
    {
        return Html::el('span')
            ->addAttributes(['class' => 'badge bg-color-10'])
            ->addText(
                _('Participant') . ' - ' . _($this->eventParticipant->status->value)
            );
    }
}
