<?php

namespace FKSDB\Model\ORM\Services;

use FKSDB\Model\ORM\DbNames;
use FKSDB\Model\ORM\Models\ModelLogin;
use Nette\Database\Context;
use Nette\Database\IConventions;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceLogin extends AbstractServiceSingle {

    public function __construct(Context $connection, IConventions $conventions) {
        parent::__construct($connection, $conventions, DbNames::TAB_LOGIN, ModelLogin::class);
    }
}
