<?php

namespace FKSDB\Models\Authorization;

use FKSDB\Models\Events\Model\Holder\Holder;
use FKSDB\Models\Transitions\Machine\Machine;
use Nette\Security\User;
use Nette\SmartObject;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class RelatedPersonAuthorizator {

    use SmartObject;

    private User $user;

    public function __construct(User $user) {
        $this->user = $user;
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
        $login= $this->user->getIdentity();
        // further on only logged users can be related person
        if (!$login) {
            return false;
        }

        $person = $login->getPerson();
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
