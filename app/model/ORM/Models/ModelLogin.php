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
 * @property-read boolean active
 * @property-read integer login_id
 * @property-read DateTime last_login
 * @property-read string hash
 * @property-read ActiveRow person
 * @property-read string login
 */
class ModelLogin extends AbstractModelSingle implements IIdentity, IPersonReferencedModel {


    /**
     * @var boolean
     */
    const NO_ACL_ROLES = true;
    /**
     * @var YearCalculator|null
     */
    private $yearCalculator;

    /**
     * @return null|YearCalculator
     */
    protected function getYearCalculator() {
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
    public function getActiveOrgs(YearCalculator $yearCalculator) {
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

    /**
     * @param $yearCalculator
     * @return bool
     */
    public function isOrg($yearCalculator): bool {
        return count($this->getActiveOrgs($yearCalculator)) > 0;
    }

    /**
     * @param $yearCalculator
     * @return bool
     */
    public function isContestant($yearCalculator) {
        $person = $this->getPerson();
        if ($person && count($person->getActiveContestants($yearCalculator)) > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Syntactic sugar.
     *
     * @return string Human readable identification of the login.
     */
    public function getName(): string {
        $person = $this->getPerson();
        if ($person) {
            return (string)$person;
        }
        if ($this->login) {
            return $this->login;
        } else {
            return 'NAMELESS LOGIN';
        }
    }

    /**
     * @return string
     */
    public function __toString() {
        return $this->getName();
    }

    /**
     * Sets hash of the instance with correct hashing function.
     *
     * @note Must be called after setting login_id.
     *
     * @param string $password password
     */
    public function setHash($password) {
        $this->hash = PasswordAuthenticator::calculateHash($password, $this);
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

            if (!$this->yearCalculator) {
                throw new InvalidStateException('To obtain current roles, you have to inject FKSDB\YearCalculator to this Login instance.');
            }
            $this->roles = [];
            $this->roles[] = new Grant(Grant::CONTEST_ALL, ModelRole::REGISTERED);

            // explicitly assigned roles
            foreach ($this->related(DbNames::TAB_GRANT, 'login_id') as $row) {
                $grant = ModelGrant::createFromActiveRow($row);
                $this->roles[] = new Grant($grant->contest_id, $grant->ref(DbNames::TAB_ROLE, 'role_id')->name);
            }
            $this->noACLRoles = [];

            // roles from other tables
            $person = $this->getPerson();
            if ($person) {
                foreach ($person->getActiveOrgs($this->yearCalculator) as $org) {
                    $grant = new Grant($org->contest_id, ModelRole::ORG);
                    $this->noACLRoles[] = $grant;
                    $this->roles[] = $grant;
                }
                foreach ($person->getActiveContestants($this->yearCalculator) as $contestant) {
                    $grant = new Grant($contestant->contest_id, ModelRole::CONTESTANT);
                    $this->noACLRoles[] = $grant;
                    $this->roles[] = $grant;
                }
            }
        }
        return $noACLRoles ? $this->noACLRoles : $this->roles;
    }

}
