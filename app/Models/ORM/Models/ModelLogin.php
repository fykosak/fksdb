<?php

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\AbstractModel;
use FKSDB\Models\Authentication\PasswordAuthenticator;
use FKSDB\Models\Authorization\Grant;
use FKSDB\Models\ORM\DbNames;
use Nette\Database\Table\ActiveRow;
use Nette\Security\IIdentity;

/**
 * @property-read bool active
 * @property-read int login_id
 * @property-read \DateTimeInterface last_login
 * @property-read string hash
 * @property-read ActiveRow person
 * @property-read string login
 */
class ModelLogin extends AbstractModel implements IIdentity
{

    public function getPerson(): ?ModelPerson
    {
        if ($this->person) {
            return ModelPerson::createFromActiveRow($this->person);
        }
        return null;
    }

    /**
     * @return ModelOrg[] indexed by contest_id (i.e. impersonal orgs)
     */
    public function getActiveOrgs(): array
    {
        if ($this->getPerson()) {
            return $this->getPerson()->getActiveOrgs();
        } else {
            $result = [];
            foreach ($this->getRoles() as $grant) {
                if ($grant->getRoleId() == ModelRole::ORG) {
                    $result[$grant->getContestId()] = null;
                }
            }
            return $result;
        }
    }

    public function isOrg(): bool
    {
        return count($this->getActiveOrgs()) > 0;
    }

    public function isContestant(): bool
    {
        $person = $this->getPerson();
        return $person && count($person->getActiveContestants()) > 0;
    }

    public function __toString(): string
    {
        $person = $this->getPerson();
        if ($person) {
            return $person->__toString();
        }
        if ($this->login) {
            return $this->login;
        } else {
            return 'NAMELESS LOGIN';
        }
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
            $this->roles[] = new Grant(Grant::CONTEST_ALL, ModelRole::REGISTERED, null);

            // explicitly assigned roles
            foreach ($this->related(DbNames::TAB_GRANT, 'login_id') as $row) {
                $grant = ModelGrant::createFromActiveRow($row);
                $this->roles[] = new Grant($grant->contest_id, $grant->role->name, $grant->getContest());
            }
            // roles from other tables
            $person = $this->getPerson();
            if ($person) {
                foreach ($person->getActiveOrgs() as $org) {
                    $this->roles[] = new Grant(
                        $org->contest_id,
                        ModelRole::ORG,
                        $org->getContest(),
                    );
                }
                foreach ($person->getActiveContestants() as $contestant) {
                    $this->roles[] = new Grant(
                        $contestant->contest_id,
                        ModelRole::CONTESTANT,
                        $contestant->getContest(),
                    );
                }
            }
        }
        return $this->roles;
    }
}
