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
     * Sets hash of the instance with correct hashing function.
     * 
     * @note Must be called after setting login_id.
     * 
     * @param string $password password
     */
    public function setHash($password) {
        $this->hash = PasswordAuthenticator::calculateHash($password, $this);
    }

    public function resetPassword() {
        //TODO
    }

    public function sendResetNotification() {
        //TODO
    }

    public function sendCreateNotification() {
        //TODO
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
