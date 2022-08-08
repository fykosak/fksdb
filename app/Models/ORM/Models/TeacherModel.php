<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Nette\Security\Resource;
use Fykosak\NetteORM\Model;

/**
 * @property-read int teacher_id
 * @property-read int person_id
 * @property-read PersonModel person
 * @property-read int school_id
 * @property-read SchoolModel school
 * @property-read \DateTimeInterface since
 * @property-read \DateTimeInterface until
 * @property-read string note
 * @property-read string state TODO ENUM('proposal','cooperate','ended','undefined') NOT NULL DEFAULT 'undefined',
 * @property-read int number_brochures
 */
class TeacherModel extends Model implements Resource
{

    public const RESOURCE_ID = 'teacher';

    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }
}
