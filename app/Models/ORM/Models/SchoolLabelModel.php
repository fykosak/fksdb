<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\Model\Model;
use Nette\Security\Resource;

/**
 * @property-read int $school_label_id
 * @property-read string $school_label_key
 * @property-read int|null $school_id
 * @property-read SchoolModel|null $school
 */
final class SchoolLabelModel extends Model implements Resource
{
    public const RESOURCE_ID = 'schoolLabel';

    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }
}
