<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Nette\Database\Table\ActiveRow;
use Nette\Security\Resource;
use Fykosak\NetteORM\Model;

/**
 * @property-read \DateTimeInterface until
 * @property-read \DateTimeInterface since
 * @property-read int school_id
 * @property-read int person_id
 * @property-read ModelPerson person
 * @property-read ModelSchool school
 * @property-read string state
 * @property-read int number_brochures
 * @property-read string note
 * @property-read int teacher_id
 */
class ModelTeacher extends Model implements Resource
{

    public const RESOURCE_ID = 'teacher';

    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }
}
