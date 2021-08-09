<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\AbstractModel;
use Nette\Database\Table\ActiveRow;
use Nette\Security\Resource;

/**
 * @property-read \DateTimeInterface until
 * @property-read \DateTimeInterface since
 * @property-read int school_id
 * @property-read int person_id
 * @property-read ActiveRow person
 * @property-read ActiveRow school
 * @property-read string state
 * @property-read int number_brochures
 * @property-read string note
 * @property-read int teacher_id
 */
class ModelTeacher extends AbstractModel implements Resource
{

    public const RESOURCE_ID = 'teacher';

    public function getPerson(): ModelPerson
    {
        return ModelPerson::createFromActiveRow($this->person);
    }

    public function getSchool(): ModelSchool
    {
        return ModelSchool::createFromActiveRow($this->school);
    }

    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }
}
