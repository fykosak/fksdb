<?php

namespace FKSDB\Models\ORM\Services\Events;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\Events\ModelDsefGroup;
use FKSDB\Models\ORM\Services\AbstractServiceSingle;
use Nette\Database\Context;
use Nette\Database\IConventions;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceDsefGroup extends AbstractServiceSingle {

    public function __construct(Context $connection, IConventions $conventions) {
        parent::__construct(DbNames::TAB_E_DSEF_GROUP, ModelDsefGroup::class, $connection, $conventions);
    }
}
