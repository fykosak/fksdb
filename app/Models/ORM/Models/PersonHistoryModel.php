<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\ORM\Tests\PersonHistory\SetSchoolTest;
use FKSDB\Models\ORM\Tests\PersonHistory\StudyTypeTest;
use FKSDB\Models\ORM\Tests\Test;
use Fykosak\NetteORM\Model\Model;
use Nette\DI\Container;

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
    public function &__get(string $key) // phpcs:ignore
    {
        $value = parent::__get($key);
        switch ($key) {
            case 'study_year_new':
                $value = StudyYear::from($value);
                break;
        }
        return $value;
    }

    public function getGraduationYear(): ?int
    {
        return $this->study_year_new->getGraduationYear($this->ac_year);
    }
    /**
     * @phpstan-return Test<self>[]
     */
    public static function getTests(Container $container): array
    {
        return [
            new StudyTypeTest($container),
            new SetSchoolTest($container),
        ];
    }
}
