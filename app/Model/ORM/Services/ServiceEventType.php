<?php

namespace FKSDB\Model\ORM\Services;

use FKSDB\ORM\DeprecatedLazyService;
use FKSDB\Model\ORM\DbNames;
use FKSDB\Model\ORM\Models\ModelEventType;
use Nette\Database\Context;
use Nette\Database\IConventions;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceEventType extends AbstractServiceSingle {
    use DeprecatedLazyService;

    public function __construct(Context $connection, IConventions $conventions) {
        parent::__construct($connection, $conventions, DbNames::TAB_EVENT_TYPE, ModelEventType::class);
    }
}
