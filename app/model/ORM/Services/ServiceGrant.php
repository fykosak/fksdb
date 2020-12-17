<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\DbNames;
use FKSDB\ORM\DeprecatedLazyService;
use FKSDB\ORM\Models\ModelGrant;
use Nette\Database\Context;
use Nette\Database\IConventions;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceGrant extends AbstractServiceSingle {
    use DeprecatedLazyService;

    public function __construct(Context $connection, IConventions $conventions) {
        parent::__construct($connection, $conventions, DbNames::TAB_GRANT, ModelGrant::class);
    }
}
