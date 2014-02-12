<?php

use Authentication\PasswordAuthenticator;
use Authorization\Grant;
use Nette\InvalidStateException;
use Nette\Security\IIdentity;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ModelLogin extends AbstractModelSingle implements IIdentity {

    /**
     * @var YearCalculator|null
     */
    private $yearCalculator;

    /**
     * @var ModelPerson|null|false
     */
    private $person = false;

    /**
     * @var array
     */
    private $availableContests = array();

    protected function getYearCalculator() {
        return $this->yearCalculator;
    }

    public function injectYearCalculator(YearCalculator $yearCalculator) {
        $this->yearCalculator = $yearCalculator;
    }

    /**
     * @return ModelPerson
     */
    public function getPerson() {
        if ($this->person === false) {
            $row = $this->ref(DbNames::TAB_PERSON, 'person_id');
            $this->person = $row ? ModelPerson::createFromTableRow($row) : null;
        }

        return $this->person;
    }

    /**
     * @param YearCalculator $yearCalculator
     * @return array of ModelOrg|null indexed by contest_id (i.e. impersonal orgs)
     */
    public function getActiveOrgs(YearCalculator $yearCalculator) {
        if ($this->getPerson()) {
            return $this->getPerson()->getActiveOrgs($yearCalculator);
        } else {
            $result = array();
            foreach ($this->getRoles() as $grant) {
                if ($grant->getRoleId() == ModelRole::ORG) {
                    $result[$grant->getContestId()] = null;
                }
            }
            return $result;
        }
    }

    public function getAvailableContests($role, $serviceContest) {
        if (!array_key_exists($role, $this->availableContests)) {
            $this->availableContests[$role] = array();
            $result = & $this->availableContests[$role];
            if ($role == ModelRole::ORG) {
                foreach ($this->getActiveOrgs($this->yearCalculator) as $contestId => $_) {
                    $result[] = $serviceContest->findByPrimary($contestId);
                }
            } else if ($role == ModelRole::CONTESTANT && $this->getPerson()) {
                foreach ($this->getPerson()->getActiveContestants($this->yearCalculator) as $contestId => $_) {
                    $result[] = $serviceContest->findByPrimary($contestId);
                }
            }
        }
        return $this->availableContests[$role];
    }

    public function isOrg($yearCalculator) {
        return count($this->getActiveOrgs($yearCalculator)) > 0;
    }

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
    public function getName() {
        $person = $this->getPerson();
        if ($person) {
            return (string) $person;
        }
        if ($this->login) {
            return $this->login;
        } else {
            return 'NAMELESS LOGIN';
        }
    }

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

    public function getId() {
        return $this->login_id;
    }

    /**
     * @var array   cache
     */
    private $roles;

    public function getRoles() {
        if ($this->roles === null) {
            if (!$this->yearCalculator) {
                throw new InvalidStateException('To obtain current roles, you have to inject YearCalculator to this Login instance.');
            }
            $this->roles = array();
            /* TODO 'registered' role, should be returned always, but consider whether it cannot happen
             * that Identity is known, however user is not logged in.
             */

            // explicitly assigned roles
            foreach ($this->related(DbNames::TAB_GRANT, 'login_id') as $grant) {
                $this->roles[] = new Grant($grant->contest_id, $grant->ref(DbNames::TAB_ROLE, 'role_id')->name);
            }
            // roles from other tables
            $person = $this->getPerson();
            if ($person) {
                foreach ($person->getActiveOrgs($this->yearCalculator) as $org) {
                    $this->roles[] = new Grant($org->contest_id, ModelRole::ORG);
                }
                foreach ($person->getActiveContestants($this->yearCalculator) as $contestant) {
                    $this->roles[] = new Grant($contestant->contest_id, ModelRole::CONTESTANT);
                }
            }
        }

        return $this->roles;
    }

}

?>
