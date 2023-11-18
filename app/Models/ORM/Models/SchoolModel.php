<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\Model\Model;
use Nette\Security\Resource;

/**
 * @property-read int $school_id
 * @property-read string|null $name_full
 * @property-read string $name
 * @property-read string $name_abbrev
 * @property-read int $address_id
 * @property-read AddressModel $address
 * @property-read string|null $email
 * @property-read string|null $ic
 * @property-read string|null $izo
 * @property-read int $active
 * @property-read string|null $note
 * @property-read int $study_h
 * @property-read int $study_p
 * @property-read int $study_u
 */
final class SchoolModel extends Model implements Resource
{

    public const RESOURCE_ID = 'school';

    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }

    public function isCzSk(): bool
    {
        return in_array($this->address->country->alpha_2, ['CZ', 'SK']);
    }

    /**
     * @phpstan-return array{
     *     schoolId:int,
     *     nameFull:string|null,
     *     name:string,
     *     nameAbbrev:string,
     *     countryISO:string,
     * }
     */
    public function __toArray(): array
    {
        return [
            'schoolId' => $this->school_id,
            'nameFull' => $this->name_full,
            'name' => $this->name,
            'nameAbbrev' => $this->name_abbrev,
            'countryISO' => $this->address->country->alpha_2,
        ];
    }
}
