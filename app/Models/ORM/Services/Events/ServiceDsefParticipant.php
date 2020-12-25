<?php

namespace FKSDB\Models\ORM\Services\Events;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\Events\ModelDsefParticipant;
use FKSDB\Models\ORM\Services\AbstractServiceSingle;
use Nette\Database\Conventions;
use Nette\Database\Explorer;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceDsefParticipant extends AbstractServiceSingle {

    public function __construct(Explorer $connection, Conventions $conventions) {
        parent::__construct(DbNames::TAB_E_DSEF_PARTICIPANT, ModelDsefParticipant::class, $connection, $conventions);
    }
}
