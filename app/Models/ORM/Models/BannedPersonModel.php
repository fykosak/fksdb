<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\Model\Model;
use Nette\Security\Resource;
use Nette\Utils\DateTime;

/**
 * @property-read int $banned_person_id
 * @property-read int $person_id
 * @property-read PersonModel $person
 * @property-read DateTime $begin
 * @property-read DateTime|null $end
 * @property-read string|null $case_id
 * @property-read string|null $note
 * @property-read array{eventTypes?:int[],contests?:int[]}|null $scope
 */
class BannedPersonModel extends Model implements Resource
{
    public const RESOURCE_ID = 'bannedPerson';

    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }

    /**
     * @param mixed $key
     * @return Model|mixed|null
     * @throws \ReflectionException
     */
    public function &__get($key)
    {
        $value = parent::__get($key);
        switch ($key) {
            case 'scope':
                $value = $value ? json_decode($value) : null;
                break;
        }
        return $value;
    }
}