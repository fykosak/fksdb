<?php

namespace FKSDB\model\Fyziklani;

use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniSubmit;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTask;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;

/**
 * Class TaskCodeHandlerFactory
 * @package FKSDB\model\Fyziklani
 */
class TaskCodeHandlerFactory {
    /**
     * @var ServiceFyziklaniSubmit
     */
    private $serviceFyziklaniSubmit;
    /**
     * @var ServiceFyziklaniTask
     */
    private $serviceFyziklaniTask;
    /**
     * @var ServiceFyziklaniTeam
     */
    private $serviceFyziklaniTeam;


    /**
     * TaskCodeHandlerFactory constructor.
     * @param ServiceFyziklaniTeam $serviceFyziklaniTeam
     * @param ServiceFyziklaniTask $serviceFyziklaniTask
     * @param ServiceFyziklaniSubmit $serviceFyziklaniSubmit
     */
    public function __construct(ServiceFyziklaniTeam $serviceFyziklaniTeam, ServiceFyziklaniTask $serviceFyziklaniTask, ServiceFyziklaniSubmit $serviceFyziklaniSubmit) {
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
        $this->serviceFyziklaniTask = $serviceFyziklaniTask;
        $this->serviceFyziklaniSubmit = $serviceFyziklaniSubmit;
    }

    /**
     * @param ModelEvent $event
     * @return SubmitHandler
     */
    public function createHandler(ModelEvent $event): SubmitHandler {
        return new SubmitHandler(
            $this->serviceFyziklaniTeam,
            $this->serviceFyziklaniTask,
            $this->serviceFyziklaniSubmit, $event
        );
    }
}
