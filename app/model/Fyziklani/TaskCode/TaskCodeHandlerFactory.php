<?php

namespace FKSDB\model\Fyziklani;

use FKSDB\ORM\ModelEvent;
use ORM\Services\Events\ServiceFyziklaniTeam;

/**
 * Class TaskCodeHandlerFactory
 * @package FKSDB\model\Fyziklani
 */
class TaskCodeHandlerFactory {
    /**
     * @var \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniSubmit
     */
    private $serviceFyziklaniSubmit;
    /**
     * @var \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTask
     */
    private $serviceFyziklaniTask;
    /**
     * @var ServiceFyziklaniTeam
     */
    private $serviceFyziklaniTeam;


    /**
     * TaskCodeHandlerFactory constructor.
     * @param ServiceFyziklaniTeam $serviceFyziklaniTeam
     * @param \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTask $serviceFyziklaniTask
     * @param \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniSubmit $serviceFyziklaniSubmit
     */
    public function __construct(ServiceFyziklaniTeam $serviceFyziklaniTeam, \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTask $serviceFyziklaniTask, \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniSubmit $serviceFyziklaniSubmit) {
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
        $this->serviceFyziklaniTask = $serviceFyziklaniTask;
        $this->serviceFyziklaniSubmit = $serviceFyziklaniSubmit;
    }

    /**
     * @param ModelEvent $event
     * @return TaskCodeHandler
     */
    public function createHandler(ModelEvent $event): TaskCodeHandler {
        return new TaskCodeHandler(
            $this->serviceFyziklaniTeam,
            $this->serviceFyziklaniTask,
            $this->serviceFyziklaniSubmit, $event
        );
    }
}
