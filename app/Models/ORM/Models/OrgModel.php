<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Nette\Security\Resource;
use Fykosak\NetteORM\Model;

/**
 * @property-read int $org_id
 * @property-read int $person_id
 * @property-read PersonModel $person
 * @property-read int $contest_id
 * @property-read ContestModel $contest
 * @property-read int $since
 * @property-read int|null until
 * @property-read string $role
 * @property-read int $order
 * @property-read string $contribution
 * @property-read string $tex_signature
 * @property-read string $domain_alias
 */
class OrgModel extends Model implements Resource
{
    public const RESOURCE_ID = 'org';

    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }
}
