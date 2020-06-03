<?php

namespace FKSDB\Components\DatabaseReflection\Tables\Schedule\ScheduleGroup;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;
use FKSDB\Exceptions\NotImplementedException;
use Nette\Forms\Controls\BaseControl;

/**
 * Class ScheduleGroupTypeRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ScheduleGroupTypeRow extends AbstractRow {
    use DefaultPrinterTrait;

    public function getTitle(): string {
        return _('Type');
    }

    protected function getModelAccessKey(): string {
        return 'schedule_group_type';
    }

    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }

    /**
     * @param array $args
     * @return BaseControl
     * @throws NotImplementedException
     */
    public function createField(...$args): BaseControl {
        throw new NotImplementedException();
    }
}
