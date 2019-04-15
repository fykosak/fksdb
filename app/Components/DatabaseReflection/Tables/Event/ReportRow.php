<?php

namespace FKSDB\Components\DatabaseReflection\Event;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextArea;

/**
 * Class ReportRow
 * @package FKSDB\Components\DatabaseReflection\Event
 */
class ReportRow extends AbstractRow {
    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Report');
    }

    /**
     * @return BaseControl
     */
    public function createField(): BaseControl {
        $control = new TextArea(self::getTitle());
        $control->setOption('description', _('Shrnující text k akci.'));
        return $control;
    }
}
