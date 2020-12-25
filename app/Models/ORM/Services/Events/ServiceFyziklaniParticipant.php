<?php

namespace FKSDB\Models\ORM\Services\Events;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\Events\ModelFyziklaniParticipant;
use FKSDB\Models\ORM\Services\AbstractServiceSingle;
use Nette\Database\Conventions;
use Nette\Database\Explorer;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceFyziklaniParticipant extends AbstractServiceSingle {

    public function __construct(Explorer $connection, Conventions $conventions) {
        parent::__construct(DbNames::TAB_E_FYZIKLANI_PARTICIPANT, ModelFyziklaniParticipant::class, $connection, $conventions);
    }
}
