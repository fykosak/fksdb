<?php

namespace FKSDB\Models\Authorization;

use FKSDB\Models\Events\Model\Holder\Holder;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\Models\ORM\Models\ModelEventParticipant;
use FKSDB\Models\ORM\ModelsMulti\Events\ModelMDsefParticipant;
use FKSDB\Models\ORM\ModelsMulti\Events\ModelMFyziklaniParticipant;
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
    public function isRelatedPerson(Holder $holder): bool
    {
        // everyone is related
        if ($holder->getPrimaryHolder()->getModelState() == Machine::STATE_INIT) {
            return true;
        }
        $login = $this->user->getIdentity();
        // further on only logged users can be related person
        if (!$login) {
            return false;
        }

        $person = $login->getPerson();
        if (!$person) {
            return false;
        }

        foreach ($holder->getBaseHolders() as $baseHolder) {
            $model = $baseHolder->getModel2();
            if ($model instanceof ModelFyziklaniTeam) {
                if ($model->teacher_id == $person->person_id) {
                    return true;
                }
            } elseif (
                $model instanceof ModelEventParticipant
                || $model instanceof ModelMFyziklaniParticipant
                || $model instanceof ModelMDsefParticipant
            ) {
                if ($model->person_id == $person->person_id) {
                    return true;
                }
            }
            /* if ($baseHolder->getPerson()->person_id == $person->person_id) {
                 return true;
             }*/
        }
        return false;
    }
}
