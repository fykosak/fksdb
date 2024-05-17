<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Spam;

use FKSDB\Models\ORM\Models\SchoolModel;
use Fykosak\NetteORM\Model\Model;
use Nette\Security\Resource;

/**
 * @property-read string $spam_school_label
 * @property-read int|null $school_id
 * @property-read SchoolModel|null $school
 */
final class SpamSchoolModel extends Model implements Resource
{
    public const RESOURCE_ID = 'spamSchool';

    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }
}
