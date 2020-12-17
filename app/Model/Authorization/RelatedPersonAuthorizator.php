<?php

namespace FKSDB\Model\Authorization;

use FKSDB\Model\Events\Model\Holder\Holder;
use FKSDB\Model\Transitions\Machine\Machine;
use Nette\Security\IUserStorage;
use Nette\SmartObject;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class RelatedPersonAuthorizator {

    use SmartObject;

    private IUserStorage $user;

    public function __construct(IUserStorage $user) {
        $this->user = $user;
    }

    public function getUser(): IUserStorage {
        return $this->user;
    }

    /**
     * User must posses the role (for the resource:privilege) in the context
     * of the queried contest.
     *
     * @param Holder $holder
     * @return bool
     */
    public function isRelatedPerson(Holder $holder): bool {
        // everyone is related
        if ($holder->getPrimaryHolder()->getModelState() == Machine::STATE_INIT) {
            return true;
        }

        // further on only logged users can be related person
        if (!$this->getUser()->isAuthenticated()) {
            return false;
        }

        $person = $this->getUser()->getIdentity()->getPerson();
        if (!$person) {
            return false;
        }

        foreach ($holder->getBaseHolders() as $baseHolder) {
            if ($baseHolder->getPersonId() == $person->person_id) {
                return true;
            }
        }

        return false;
    }
}