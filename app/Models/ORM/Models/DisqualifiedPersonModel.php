<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\Model\Model;
use Nette\Security\Resource;
use Nette\Utils\DateTime;

/**
 * @property-read int $disqualified_person_id
 * @property-read int $person_id
 * @property-read PersonModel $person
 * @property-read DateTime $begin
 * @property-read DateTime|null $end
 * @property-read string|null $case_id
 * @property-read string|null $note
 */
class DisqualifiedPersonModel extends Model implements Resource
{
    public const RESOURCE_ID = 'disqualifiedPerson';

    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }
}