<?php

namespace FKSDB\model\Fyziklani\Rooms;

use FKS\Logging\MemoryLogger;
use ModelEvent;
use ORM\Services\Events\ServiceFyziklaniTeam;
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

    function __construct(ServiceFyziklaniTeam $serviceTeam) {
        $this->serviceTeam = $serviceTeam;
    }

    /**
     * 
     * @return Pipeline
     */
    public function create(ModelEvent $event) {
        $pipeline = new Pipeline();
        $pipeline->setLogger(new MemoryLogger());

        // common stages
        $stage = new RoomsFromCSV($event, $this->serviceTeam);
        $pipeline->addStage($stage);

        return $pipeline;
    }

}
