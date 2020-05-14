<?php

namespace FKSDB\Fyziklani\Rooms;

use FKSDB\Logging\MemoryLogger;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use Pipeline\Pipeline;

/**
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class PipelineFactory {

    /**
     * @var ServiceFyziklaniTeam
     */
    private $serviceTeam;

    /**
     * PipelineFactory constructor.
     * @param ServiceFyziklaniTeam $serviceTeam
     */
    public function __construct(ServiceFyziklaniTeam $serviceTeam) {
        $this->serviceTeam = $serviceTeam;
    }

    /**
     * @param ModelEvent $event
     * @return Pipeline
     */
    public function create(ModelEvent $event): Pipeline {
        $pipeline = new Pipeline();
        $pipeline->setLogger(new MemoryLogger());

        // common stages
        $stage = new RoomsFromCSV($event, $this->serviceTeam);
        $pipeline->addStage($stage);

        return $pipeline;
    }

}
