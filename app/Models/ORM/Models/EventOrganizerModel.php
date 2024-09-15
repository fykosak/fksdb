<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\Authorization\Resource\EventResource;
use Fykosak\NetteORM\Model\Model;

/**
 * @property-read int $e_org_id
 * @property-read string $note
 * @property-read int $event_id
 * @property-read EventModel $event
 * @property-read int $person_id
 * @property-read PersonModel $person
 */
final class EventOrganizerModel extends Model implements EventResource
{

    public const RESOURCE_ID = 'event.organizer';

    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }

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

    public function getContest(): ContestModel
    {
        return $this->getContestYear()->contest;
    }

    public function getContestYear(): ContestYearModel
    {
        return $this->getEvent()->getContestYear();
    }
}
