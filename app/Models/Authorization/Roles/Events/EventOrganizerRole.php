<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Roles\Events;

use FKSDB\Models\Authorization\Roles\ImplicitRole;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventOrganizerModel;
use Fykosak\NetteORM\Model\Model;
use Nette\Utils\Html;

final class EventOrganizerRole implements EventRole, ImplicitRole
{
    public const RoleId = 'event.organizer'; // phpcs:ignore
    private EventOrganizerModel $eventOrganizer;

    public function __construct(EventOrganizerModel $eventOrganizer)
    {
        $this->eventOrganizer = $eventOrganizer;
    }

    public function badge(): Html
    {
        return Html::el('span')
            ->addAttributes(['class' => 'badge bg-color-7'])
            ->addText(sprintf(_('Event organizer: %s'), $this->eventOrganizer->note));
    }

    public function getRoleId(): string
    {
        return self::RoleId;
    }

    public function getEvent(): EventModel
    {
        return $this->eventOrganizer->event;
    }

    public function getModel(): EventOrganizerModel
    {
        return $this->eventOrganizer;
    }
}
