<?php

namespace FKSDB\Model\Fyziklani\Submit;

use FKSDB\Model\ORM\Models\ModelEvent;
use FKSDB\Model\ORM\Services\Fyziklani\ServiceFyziklaniSubmit;
use FKSDB\Model\ORM\Services\Fyziklani\ServiceFyziklaniTask;
use FKSDB\Model\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use Nette\Security\User;

/**
 * Class HandlerFactory
 * @author Michal Červeňák <miso@fykos.cz>
 */
class HandlerFactory {

    private ServiceFyziklaniSubmit $serviceFyziklaniSubmit;
    private ServiceFyziklaniTask $serviceFyziklaniTask;
    private ServiceFyziklaniTeam $serviceFyziklaniTeam;
    private User $user;

    public function __construct(
        ServiceFyziklaniTeam $serviceFyziklaniTeam,
        ServiceFyziklaniTask $serviceFyziklaniTask,
        ServiceFyziklaniSubmit $serviceFyziklaniSubmit,
        User $user
    ) {
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
        $this->serviceFyziklaniTask = $serviceFyziklaniTask;
        $this->serviceFyziklaniSubmit = $serviceFyziklaniSubmit;
        $this->user = $user;
    }

    public function create(ModelEvent $event): Handler {
        return new Handler($event, $this->serviceFyziklaniTeam, $this->serviceFyziklaniTask, $this->serviceFyziklaniSubmit, $this->user);
    }
}