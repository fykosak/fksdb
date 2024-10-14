<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\Authorization\Resource\ContestResource;
use FKSDB\Models\Authorization\Roles\ContestRole;
use Fykosak\NetteORM\Model\Model;
use Nette\InvalidArgumentException;
use Nette\Utils\Html;

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
final class OrganizerModel extends Model implements ContestResource, ContestRole
{
    public const Resourceid = 'organizer';// phpcs:ignore
    public const RoleId = 'contest.organiser'; // phpcs:ignore

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

    public function getRoleId(): string
    {
        return self::RoleId;
    }

    public function getResourceId(): string
    {
        return self::Resourceid;
    }

    public function badge(): Html
    {
        return Html::el('span')
            ->addAttributes(['class' => 'me-2 badge bg-primary '])
            ->addText($this->label() . ' (' . $this->description() . ')');
    }

    public function description(): string
    {
        return 'základní role organizátora';
    }

    public function label(): string
    {
        return 'Organizer';
    }
}
