<?php

namespace FKSDB\ORM\Services;


use FKSDB\ORM\DbNames;
use FKSDB\ORM\DeprecatedLazyService;
use FKSDB\ORM\Models\ModelStudyYear;
use Nette\Database\Context;
use Nette\Database\IConventions;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceStudyYear extends AbstractServiceSingle {
    use DeprecatedLazyService;

    public function __construct(Context $connection, IConventions $conventions) {
        parent::__construct($connection, $conventions, DbNames::TAB_STUDY_YEAR, ModelStudyYear::class);
    }
}
