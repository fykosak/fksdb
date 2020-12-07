<?php

namespace FKSDB\Model\ORM\Services\Events;

use FKSDB\Model\ORM\DbNames;
use FKSDB\Model\ORM\Models\Events\ModelDsefParticipant;
use FKSDB\Model\ORM\Services\AbstractServiceSingle;
use Nette\Database\Context;
use Nette\Database\IConventions;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceDsefParticipant extends AbstractServiceSingle {

    public function __construct(Context $connection, IConventions $conventions) {
        parent::__construct($connection, $conventions, DbNames::TAB_E_DSEF_PARTICIPANT, ModelDsefParticipant::class);
    }
}
