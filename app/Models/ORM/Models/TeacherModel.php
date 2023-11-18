<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\Model\Model;
use Nette\Security\Resource;

/**
 * @property-read int $teacher_id
 * @property-read int $person_id
 * @property-read PersonModel $person
 * @property-read int|null $school_id
 * @property-read SchoolModel|null $school
 * @property-read string|null $note
 * @property-read string|null $role
 * @property-read int $active
 */
final class TeacherModel extends Model implements Resource
{

    public const RESOURCE_ID = 'teacher';

    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }
}
