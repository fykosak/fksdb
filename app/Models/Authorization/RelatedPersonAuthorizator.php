<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization;

use FKSDB\Models\Events\Model\Holder\Holder;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\Fyziklani\TeamTeacherModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\ModelsMulti\Events\ModelMDsefParticipant;
use FKSDB\Models\ORM\ModelsMulti\Events\ModelMFyziklaniParticipant;
use FKSDB\Models\Transitions\Machine\AbstractMachine;
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
        if ($holder->primaryHolder->getModelState() == AbstractMachine::STATE_INIT) {
            return true;
        }
        $login = $this->user->getIdentity();
        // further on only logged users can be related person
        if (!$login) {
            return false;
        }

        $person = $login->person;
        if (!$person) {
            return false;
        }

        foreach ($holder->getBaseHolders() as $baseHolder) {
            $model = $baseHolder->getModel2();
            if ($model instanceof TeamModel) {
                if ($model->teacher_id == $person->person_id) {
                    return true;
                }
            } elseif ($model instanceof TeamModel2) {
                /** @var TeamTeacherModel $teacher */
                foreach ($model->getTeachers() as $teacher) {
                    if ($teacher->person_id == $person->person_id) {
                        return true;
                    }
                }
            } elseif (
                $model instanceof EventParticipantModel
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
