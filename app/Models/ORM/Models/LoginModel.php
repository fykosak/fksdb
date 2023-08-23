<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\Authorization\Grant;
use FKSDB\Models\ORM\DbNames;
use Fykosak\NetteORM\Model;
use Fykosak\NetteORM\TypedGroupedSelection;
use Nette\Security\IIdentity;

/**
 * @property-read int $login_id
 * @property-read int|null $person_id
 * @property-read PersonModel|null $person
 * @property-read string|null $login
 * @property-read string|null $hash
 * @property-read \DateTimeInterface $created
 * @property-read \DateTimeInterface|null $last_login
 * @property-read int $active
 */
final class LoginModel extends Model implements IIdentity
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
    public function calculateHash(string $password): string
    {
        return sha1($this->login_id . md5($password));
    }

    // ----- IIdentity implementation ----------

    public function getId(): int
    {
        return $this->login_id;
    }

    /** @phpstan-var Grant[]   cache */
    private array $roles;

    /**
     * @phpstan-return Grant[]
     */
    public function getRoles(): array
    {
        if (!isset($this->roles)) {
            // explicitly assigned roles
            $this->roles = [new Grant(RoleModel::REGISTERED, null), ...$this->createGrantModels()];

            // roles from other tables
            $person = $this->person;
            if ($person) {
                foreach ($person->getActiveOrgs() as $org) {
                    $this->roles[] = new Grant(
                        RoleModel::ORG,
                        $org->contest,
                    );
                }
                /** @var ContestantModel $contestant */
                foreach ($person->getContestants() as $contestant) {
                    $this->roles[] = new Grant(
                        RoleModel::CONTESTANT,
                        $contestant->contest,
                    );
                }
            }
        }
        return $this->roles;
    }

    /**
     * @phpstan-return TypedGroupedSelection<GrantModel>
     */
    public function getGrants(): TypedGroupedSelection
    {
        /** @phpstan-var TypedGroupedSelection<GrantModel> $selection */
        $selection = $this->related(DbNames::TAB_GRANT, 'login_id');
        return $selection;
    }

    /**
     * @phpstan-return Grant[]
     */
    public function createGrantModels(): array
    {
        $grants = [];
        /** @var GrantModel $grant */
        foreach ($this->getGrants() as $grant) {
            $grants[] = new Grant($grant->role->name, $grant->contest);
        }
        return $grants;
    }

    /**
     * @phpstan-return TypedGroupedSelection<AuthTokenModel>
     */
    public function getTokens(?AuthTokenType $type = null): TypedGroupedSelection
    {
        /** @phpstan-var TypedGroupedSelection<AuthTokenModel> $query */
        $query = $this->related(DbNames::TAB_AUTH_TOKEN, 'login_id');
        if (isset($type)) {
            $query->where('type', $type);
        }
        return $query;
    }

    /**
     * @phpstan-return TypedGroupedSelection<AuthTokenModel>
     */
    public function getActiveTokens(?AuthTokenType $type = null): TypedGroupedSelection
    {
        $query = $this->getTokens($type);
        $query->where('until > ?', new \DateTime());
        return $query;
    }
}
