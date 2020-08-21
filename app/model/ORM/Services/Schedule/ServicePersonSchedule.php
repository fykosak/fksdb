<?php

namespace FKSDB\ORM\Services\Schedule;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\DeprecatedLazyService;
use FKSDB\ORM\Models\Schedule\ModelPersonSchedule;
use Nette\Database\Context;
use Nette\Database\IConventions;

/**
 * Class ServicePersonSchedule
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ServicePersonSchedule extends AbstractServiceSingle {
    use DeprecatedLazyService;

    /**
     * ServicePersonSchedule constructor.
     * @param Context $connection
     * @param IConventions $conventions
     */
    public function __construct(Context $connection, IConventions $conventions) {
        parent::__construct($connection, $conventions, DbNames::TAB_PERSON_SCHEDULE, ModelPersonSchedule::class);
    }
}
