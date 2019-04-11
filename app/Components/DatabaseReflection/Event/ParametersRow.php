<?php

namespace FKSDB\Components\DatabaseReflection\Event;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextArea;

/**
 * Class ParametersRow
 * @package FKSDB\Components\DatabaseReflection\Event
 */
class ParametersRow extends AbstractRow {
    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }

    /**
     * @return string
     */
    public static function getTitle(): string {
        return _('Parameters');
    }

    /**
     * @return BaseControl
     */
    public function createField(): BaseControl {
        $control = new TextArea(self::getTitle());
        $control->setOption('description', _('V Neon syntaxi, schéma je specifické pro definici akce.'));
        return $control;
    }
}
