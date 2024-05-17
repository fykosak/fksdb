<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Spam;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\SchoolModel;
use FKSDB\Models\ORM\Models\StudyYear;
use Fykosak\NetteORM\Model\Model;
use Nette\Security\Resource;
use Nette\SmartObject;

/**
 * @property-read int $spam_person_id
 * @property-read string $other_name
 * @property-read string $family_name
 * @property-read \DateTimeInterface $created
 * @property-read StudyYear $study_year_new
 * @property-read string $spam_school_label
 * @property-read SpamSchoolModel $spam_school
 */
final class SpamPersonModel extends Model implements Resource
{
    public const RESOURCE_ID = 'spamPerson';

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

    public function getFullName(): string
    {
        return $this->other_name . " " . $this->family_name;
    }

    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }
}
