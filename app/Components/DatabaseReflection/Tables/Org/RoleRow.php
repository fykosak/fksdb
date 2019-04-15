<?php

namespace FKSDB\Components\DatabaseReflection\Org;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;

/**
 * Class RoleRow
 * @package FKSDB\Components\DatabaseReflection\Org
 */
class RoleRow extends AbstractRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Role');
    }

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
        $control = parent::createField();
        $control->addRule(Form::MAX_LENGTH, null, 255);
        return $control;
    }
}
