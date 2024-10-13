<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\Authorization\Resource\ContestResource;
use FKSDB\Models\UI\EmailPrinter;
use Fykosak\NetteORM\Model\Model;
use Nette\InvalidArgumentException;

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
 * @property-read int $allow_wiki
 * @property-read int $allow_pm
 */
final class OrganizerModel extends Model implements ContestResource
{
    public const RESOURCE_ID = 'organizer';

    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }

    public function isActive(ContestYearModel $contestYear): bool
    {
        if ($contestYear->contest_id !== $this->contest_id) {
            throw new InvalidArgumentException();
        }
        return $this->since <= $contestYear->year && ($this->until === null || $this->until >= $contestYear->year);
    }

    public function getContest(): ContestModel
    {
        return $this->contest;
    }

    public function formatDomainEmail(): ?string
    {
        switch ($this->contest_id) {
            case ContestModel::ID_FYKOS:
                return $this->domain_alias . '@fykos.cz';
            case ContestModel::ID_VYFUK:
                return $this->domain_alias . '@vyfuk.org';
        }
        return null;
    }
}
