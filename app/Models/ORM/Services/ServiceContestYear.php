<?php

namespace FKSDB\Models\ORM\Services;



use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\ModelContestYear;
use Nette\Database\Context;
use Nette\Database\IConventions;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceContestYear extends AbstractServiceSingle {


    public function __construct(Context $connection, IConventions $conventions) {
        parent::__construct($connection, $conventions, DbNames::TAB_CONTEST_YEAR, ModelContestYear::class);
    }
}
