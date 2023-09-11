<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\Model;
use Nette\Security\Resource;

/**
 * @property-read int $org_id
 * @property-read int $person_id
 * @property-read PersonModel $person
 * @property-read int $contest_id
 * @property-read ContestModel $contest
 * @property-read int $since
 * @property-read int|null $until
 * @property-read string|null $role
 * @property-read int $order
 * @property-read string|null $contribution
 * @property-read string|null $tex_signature
 * @property-read string|null $domain_alias
 */
final class OrganizerModel extends Model implements Resource
{
    public const RESOURCE_ID = 'org';

    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }
}
