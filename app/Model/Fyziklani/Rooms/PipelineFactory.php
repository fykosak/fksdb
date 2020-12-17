<?php

namespace FKSDB\Model\Fyziklani\Rooms;

use FKSDB\Model\Logging\MemoryLogger;
use FKSDB\Model\ORM\Models\ModelEvent;
use FKSDB\Model\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use FKSDB\Model\Pipeline\Pipeline;

/**
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class PipelineFactory {

    private ServiceFyziklaniTeam $serviceTeam;

    public function __construct(ServiceFyziklaniTeam $serviceTeam) {
        $this->serviceTeam = $serviceTeam;
    }

    public function create(ModelEvent $event): Pipeline {
        $pipeline = new Pipeline();
        $pipeline->setLogger(new MemoryLogger());

        // common stages
        $stage = new RoomsFromCSV($event, $this->serviceTeam);
        $pipeline->addStage($stage);

        return $pipeline;
    }

}
