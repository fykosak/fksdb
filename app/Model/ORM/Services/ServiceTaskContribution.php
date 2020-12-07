<?php

namespace FKSDB\Model\ORM\Services;


use FKSDB\Model\ORM\DbNames;
use FKSDB\Model\ORM\DeprecatedLazyDBTrait;
use FKSDB\Model\ORM\Models\ModelTaskContribution;
use Nette\Database\Context;
use Nette\Database\IConventions;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceTaskContribution extends AbstractServiceSingle {
    use DeprecatedLazyDBTrait;

    public function __construct(Context $connection, IConventions $conventions) {
        parent::__construct($connection, $conventions, DbNames::TAB_TASK_CONTRIBUTION, ModelTaskContribution::class);
    }
}
