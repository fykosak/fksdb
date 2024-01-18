<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\EventRole;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use Nette\Utils\Html;

final class ParticipantRole extends EventRole
{
    public const ROLE_ID = 'event.participant';
    public EventParticipantModel $eventParticipant;

    public function __construct(EventModel $event, EventParticipantModel $eventParticipant)
    {
        parent::__construct(self::ROLE_ID, $event);
        $this->eventParticipant = $eventParticipant;
    }

    public function badge(): Html
    {
        return Html::el('span')
            ->addAttributes(['class' => 'badge bg-color-10'])
            ->addText(sprintf(_('Participant (%s)'), $this->eventParticipant->status->label()));
    }
}
