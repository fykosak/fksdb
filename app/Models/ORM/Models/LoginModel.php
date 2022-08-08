<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\Model;
use FKSDB\Models\Authentication\PasswordAuthenticator;
use FKSDB\Models\Authorization\Grant;
use FKSDB\Models\ORM\DbNames;
use Nette\Security\IIdentity;

/**
 * @property-read int login_id
 * @property-read int|null person_id
 * @property-read PersonModel|null person
 * @property-read string login
 * @property-read string hash
 * @property-read \DateTimeInterface created
 * @property-read \DateTimeInterface last_login
 * @property-read bool active
 */
class LoginModel extends Model implements IIdentity
{
    public function __toString(): string
    {
        return $this->person ? $this->person->__toString() : ($this->login ?? 'NAMELESS LOGIN');
    }

    /**
     * Sets hash of the instance with correct hashing function.
     *
     * @note Must be called after setting login_id.
     */
    public function createHash(string $password): string
    {
        return PasswordAuthenticator::calculateHash($password, $this);
    }

    // ----- IIdentity implementation ----------

    public function getId(): int
    {
        return $this->login_id;
    }

    /** @var Grant[]   cache */
    private array $roles;

    /**
     * @return Grant[]
     */
    public function getRoles(): array
    {
        if (!isset($this->roles)) {
            $this->roles = [];
            $this->roles[] = new Grant(RoleModel::REGISTERED, null);

            // explicitly assigned roles
            foreach ($this->related(DbNames::TAB_GRANT, 'login_id') as $row) {
                $grant = GrantModel::createFromActiveRow($row);
                $this->roles[] = new Grant($grant->role->name, $grant->contest);
            }
            // roles from other tables
            $person = $this->person;
            if ($person) {
                foreach ($person->getActiveOrgs() as $org) {
                    $this->roles[] = new Grant(
                        RoleModel::ORG,
                        $org->contest,
                    );
                }
                foreach ($person->getActiveContestants() as $contestant) {
                    $this->roles[] = new Grant(
                        RoleModel::CONTESTANT,
                        $contestant->contest,
                    );
                }
            }
        }
        return $this->roles;
    }
}
