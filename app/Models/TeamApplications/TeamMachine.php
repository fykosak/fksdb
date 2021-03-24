<?php

namespace FKSDB\Models\TeamApplications;

use FKSDB\Models\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Machine\Machine;
use Fykosak\NetteORM\AbstractModel;

/**
 * Class TeamMachine
 * @package FKSDB\Models\TeamApplications
 * @property ServiceFyziklaniTeam $service
 */
class TeamMachine extends Machine {

    public function createHolder(?AbstractModel $model): ModelHolder {
        return new TeamApplicationHolder($model, $this->service);
    }
}
