<?php

namespace FKSDB\Model\ORM\Services\Schedule;

use FKSDB\ORM\DeprecatedLazyService;
use FKSDB\Model\ORM\DbNames;
use FKSDB\Model\ORM\Models\Schedule\ModelPersonSchedule;
use FKSDB\Model\ORM\Services\AbstractServiceSingle;
use Nette\Database\Context;
use Nette\Database\IConventions;

/**
 * Class ServicePersonSchedule
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ServicePersonSchedule extends AbstractServiceSingle {
    use DeprecatedLazyService;

    public function __construct(Context $connection, IConventions $conventions) {
        parent::__construct($connection, $conventions, DbNames::TAB_PERSON_SCHEDULE, ModelPersonSchedule::class);
    }
}
