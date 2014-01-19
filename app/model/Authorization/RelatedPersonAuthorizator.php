<?php

namespace Authorization;

use Events\Machine\BaseMachine;
use Events\Model\Holder\Holder;
use ModelContest;
use Nette\Object;
use Nette\Security\User;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class RelatedPersonAuthorizator extends Object {

    /**
     * @var User
     */
    private $user;

    function __construct(User $user) {
        $this->user = $user;
    }

    public function getUser() {
        return $this->user;
    }

    /**
     * User must posses the role (for the resource:privilege) in the context
     * of the queried contest.
     * 
     * @param mixed $resource
     * @param enum $privilege
     * @param int|ModelContest $contest queried contest
     * @return boolean
     */
    public function isRelatedPerson(Holder $holder) {
        // everyone is related
        if ($holder->getPrimaryHolder()->getModelState() == BaseMachine::STATE_INIT) {
            return true;
        }

        // further on only logged users can be related person
        if (!$this->getUser()->isLoggedIn()) {
            return false;
        }

        $person = $this->getUser()->getIdentity()->getPerson();
        if (!$person) {
            return false;
        }

        foreach ($holder as $baseHolder) {
            if ($baseHolder->getPersonId() == $person->person_id) {
                return true;
            }
        }

        return false;
    }

}
