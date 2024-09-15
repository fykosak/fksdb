<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Roles\Events;

use FKSDB\Models\Authorization\Roles\ImplicitRole;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use Nette\Utils\Html;

final class ParticipantRole implements EventRole, ImplicitRole
{
    public const RoleId = 'event.participant'; // phpcs:ignore
    private EventParticipantModel $eventParticipant;

    public function __construct(EventParticipantModel $eventParticipant)
    {
        $this->eventParticipant = $eventParticipant;
    }

    public function badge(): Html
    {
        return Html::el('span')
            ->addAttributes(['class' => 'badge bg-color-10'])
            ->addText(sprintf(_('Participant (%s)'), $this->eventParticipant->status->label()));
    }

    public function getEvent(): EventModel
    {
        return $this->eventParticipant->event;
    }

    public function getRoleId(): string
    {
        return self::RoleId;
    }

    public function getModel(): EventParticipantModel
    {
        return $this->eventParticipant;
    }
}
