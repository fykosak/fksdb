<?php

namespace FKSDB\model\Fyziklani;

use FKSDB\ORM\ModelEvent;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;

/**
 * Class TaskCodeHandlerFactory
 * @package FKSDB\model\Fyziklani
 */
class TaskCodeHandlerFactory {
    /**
     * @var \ServiceFyziklaniSubmit
     */
    private $serviceFyziklaniSubmit;
    /**
     * @var \ServiceFyziklaniTask
     */
    private $serviceFyziklaniTask;
    /**
     * @var ServiceFyziklaniTeam
     */
    private $serviceFyziklaniTeam;


    /**
     * TaskCodeHandlerFactory constructor.
     * @param ServiceFyziklaniTeam $serviceFyziklaniTeam
     * @param \ServiceFyziklaniTask $serviceFyziklaniTask
     * @param \ServiceFyziklaniSubmit $serviceFyziklaniSubmit
     */
    public function __construct(ServiceFyziklaniTeam $serviceFyziklaniTeam, \ServiceFyziklaniTask $serviceFyziklaniTask, \ServiceFyziklaniSubmit $serviceFyziklaniSubmit) {
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
