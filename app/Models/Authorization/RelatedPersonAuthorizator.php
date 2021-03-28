<?php

namespace FKSDB\Models\Authorization;

use FKSDB\Models\Events\Model\Holder\Holder;
use FKSDB\Models\Transitions\Machine\Machine;
use Nette\Security\IUserStorage;
use Nette\SmartObject;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class RelatedPersonAuthorizator {

    use SmartObject;

    private IUserStorage $userStorage;

    public function __construct(IUserStorage $userStorage) {
        $this->userStorage = $userStorage;
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
        if (!$this->userStorage->isAuthenticated()) {
            return false;
        }

        $person = $this->userStorage->getIdentity()->getPerson();
        if (!$person) {
            return false;
        }

        foreach ($holder->getBaseHolders() as $baseHolder) {
            if ($baseHolder->getPerson()->person_id == $person->person_id) {
                return true;
            }
        }

        return false;
    }
}
