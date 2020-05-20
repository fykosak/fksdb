<?php

namespace FKSDB\ORM\Models;

use Authentication\PasswordAuthenticator;
use Authorization\Grant;
use DateTime;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\DbNames;
use FKSDB\YearCalculator;
use Nette\Database\Table\ActiveRow;
use Nette\InvalidStateException;
use Nette\Security\IIdentity;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property-read bool active
 * @property-read int login_id
 * @property-read DateTime last_login
 * @property-read string hash
 * @property-read ActiveRow person
 * @property-read string login
 */
class ModelLogin extends AbstractModelSingle implements IIdentity, IPersonReferencedModel {

    /**
     * @var YearCalculator|null
     */
    private $yearCalculator;

    /**
     * @return YearCalculator
     * @throws InvalidStateException
     * @internal
     */
    private function getYearCalculator(): YearCalculator {
        if (!$this->yearCalculator) {
            throw new InvalidStateException('To obtain current roles, you have to inject FKSDB\YearCalculator to this Login instance.');
        }
        return $this->yearCalculator;
    }

    /**
     * @param YearCalculator $yearCalculator
     */
    public function injectYearCalculator(YearCalculator $yearCalculator) {
        $this->yearCalculator = $yearCalculator;
    }

    /**
     * @return ModelPerson|null
     */
    public function getPerson() {
        if ($this->person) {
            return ModelPerson::createFromActiveRow($this->person);
        }
        return null;
    }

    /**
     * @param YearCalculator $yearCalculator
     * @return ModelOrg[] indexed by contest_id (i.e. impersonal orgs)
     */
    public function getActiveOrgs(YearCalculator $yearCalculator): array {
        if ($this->getPerson()) {
            return $this->getPerson()->getActiveOrgs($yearCalculator);
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

    public function isOrg(YearCalculator $yearCalculator): bool {
        return count($this->getActiveOrgs($yearCalculator)) > 0;
    }

    public function isContestant(YearCalculator $yearCalculator): bool {
        $person = $this->getPerson();
        return $person && count($person->getActiveContestants($yearCalculator)) > 0;
    }

    public function __toString(): string {
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
     * @param string $password
     * @return string
     */
    public function createHash(string $password): string {
        return PasswordAuthenticator::calculateHash($password, $this);
    }

    // ----- IIdentity implementation ----------

    /**
     * @return int|mixed
     */
    public function getId() {
        return $this->login_id;
    }

    /**
     * @var Grant[]   cache
     */
    private $roles;

    /**
     * @return array|Grant[]|null
     */
    public function getRoles() {
        if ($this->roles === null) {
            $this->roles = [];
            $this->roles[] = new Grant(Grant::CONTEST_ALL, ModelRole::REGISTERED);

            // explicitly assigned roles
            foreach ($this->related(DbNames::TAB_GRANT, 'login_id') as $row) {
                $grant = ModelGrant::createFromActiveRow($row);
                $this->roles[] = new Grant($grant->contest_id, $grant->ref(DbNames::TAB_ROLE, 'role_id')->name);
            }
            // roles from other tables
            $person = $this->getPerson();
            if ($person) {
                foreach ($person->getActiveOrgs($this->getYearCalculator()) as $org) {
                    $this->roles[] = new Grant($org->contest_id, ModelRole::ORG);
                }
                foreach ($person->getActiveContestants($this->getYearCalculator()) as $contestant) {
                    $this->roles[] = new Grant($contestant->contest_id, ModelRole::CONTESTANT);
                }
            }
        }

        return $this->roles;
    }

}
