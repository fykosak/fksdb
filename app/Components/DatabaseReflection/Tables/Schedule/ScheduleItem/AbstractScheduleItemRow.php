<?php

namespace FKSDB\Components\DatabaseReflection\Tables\Schedule\ScheduleItem;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use Nette\Forms\Controls\BaseControl;
use FKSDB\NotImplementedException;

/**
 * Class AbstractScheduleItemRow
 * @package FKSDB\Components\DatabaseReflection\Tables\Schedule\ScheduleItem
 */
abstract class AbstractScheduleItemRow extends AbstractRow {
    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }

    /**
     * @return BaseControl
     */
    public function createField(): BaseControl {
        throw new NotImplementedException();
    }
}
