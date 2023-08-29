<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\Model;

/**
 * @property-read int $person_history_id
 * @property-read int $person_id
 * @property-read PersonModel $person
 * @property-read int $ac_year
 * @property-read int|null $school_id
 * @property-read SchoolModel|null $school
 * @property-read string|null $class
 * @property-read StudyYear $study_year_new
 */
final class PersonHistoryModel extends Model
{
    /**
     * @return StudyYear|mixed|null
     * @throws \ReflectionException
     */
    public function &__get(string $key)
    {
        $value = parent::__get($key);
        switch ($key) {
            case 'study_year_new':
                $value = StudyYear::from($value);
                break;
        }
        return $value;
    }
}
