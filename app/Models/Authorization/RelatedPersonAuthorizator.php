<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization;

use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\LoginModel;
use FKSDB\Models\Transitions\Machine\Machine;
use Nette\Security\User;
use Nette\SmartObject;

class RelatedPersonAuthorizator
{
    use SmartObject;

    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * User must posses the role (for the resource:privilege) in the context
     * of the queried contest.
     */
    public function isRelatedPerson(BaseHolder $holder): bool
    {
        // everyone is related
        if ($holder->getModelState() == Machine::STATE_INIT) {
            return true;
        }
        /** @var LoginModel|null $login */
        $login = $this->user->getIdentity();
        // further on only logged users can be related person
        if (!$login) {
            return false;
        }

        $person = $login->person;
        if (!$person) {
            return false;
        }

        $model = $holder->getModel();
        if ($model instanceof EventParticipantModel) {
            if ($model->person_id == $person->person_id) {
                return true;
            }
        }
        return false;
    }
}
