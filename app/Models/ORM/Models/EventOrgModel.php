<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Nette\Security\Resource;
use Fykosak\NetteORM\Model;

/**
 * @property-read int e_org_id
 * @property-read string note
 * @property-read int event_id
 * @property-read EventModel event
 * @property-read int person_id
 * @property-read PersonModel person
 */
class EventOrgModel extends Model implements Resource
{

    public const RESOURCE_ID = 'event.org';

    public function getContest(): ContestModel
    {
        return $this->event->getContest();
    }

    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }

    public function __toString(): string
    {
        return $this->person->__toString();
    }
}
