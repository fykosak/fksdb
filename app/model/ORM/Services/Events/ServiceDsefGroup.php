<?php

namespace FKSDB\ORM\Services\Events;

use FKSDB\ORM\Services\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\Events\ModelDsefGroup;
use Nette\Database\Context;
use Nette\Database\IConventions;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceDsefGroup extends AbstractServiceSingle {

    public function __construct(Context $connection, IConventions $conventions) {
        parent::__construct($connection, $conventions, DbNames::TAB_E_DSEF_GROUP, ModelDsefGroup::class);
    }
}
