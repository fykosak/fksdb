<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\Authorization\Resource\EventResource;
use FKSDB\Models\Authorization\Roles\EventRole;
use Fykosak\NetteORM\Model\Model;
use Nette\Utils\Html;

/**
 * @property-read int $e_org_id
 * @property-read string $note
 * @property-read int $event_id
 * @property-read EventModel $event
 * @property-read int $person_id
 * @property-read PersonModel $person
 */
final class EventOrganizerModel extends Model implements EventResource, EventRole
{
    public const ResourceId = 'event.organizer'; // phpcs:ignore
    public const RoleId = 'event.organizer'; // phpcs:ignore

    /**
     * @throws \Throwable
     */
    public function __toString(): string
    {
        return $this->person->__toString();
    }

    public function getEvent(): EventModel
    {
        return $this->event;
    }

    public function badge(): Html
    {
        return Html::el('span')
            ->addAttributes(['class' => 'badge bg-color-7'])
            ->addText(sprintf(_('Event organizer: %s'), $this->note));
    }

    public function getRoleId(): string
    {
        return self::RoleId;
    }

    public function getResourceId(): string
    {
        return self::ResourceId;
    }
}
