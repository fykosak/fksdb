<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Nette\Database\Table\ActiveRow;
use Nette\Security\Resource;
use Fykosak\NetteORM\Model;

/**
 * @property-read ModelPerson person
 * @property-read ModelEvent event
 * @property-read string note
 * @property-read int e_org_id
 */
class ModelEventOrg extends Model implements Resource
{

    public const RESOURCE_ID = 'event.org';

    public function getContest(): ModelContest
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
