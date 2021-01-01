<?php

namespace FKSDB\Models\ORM\Services\Schedule;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\DeprecatedLazyDBTrait;
use FKSDB\Models\ORM\Models\Schedule\ModelPersonSchedule;
use FKSDB\Models\ORM\Services\AbstractServiceSingle;
use Nette\Database\Context;
use Nette\Database\IConventions;

/**
 * Class ServicePersonSchedule
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ServicePersonSchedule extends AbstractServiceSingle {
    use DeprecatedLazyDBTrait;

    public function __construct(Context $connection, IConventions $conventions) {
        parent::__construct($connection, $conventions, DbNames::TAB_PERSON_SCHEDULE, ModelPersonSchedule::class);
    }
}
