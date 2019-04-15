<?php

namespace FKSDB\Components\DatabaseReflection\Person;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use Nette\Forms\Controls\BaseControl;

/**
 * Class DisplayNameRow
 * @package FKSDB\Components\DatabaseReflection\Person
 */
class DisplayNameRow extends AbstractRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Display name');
    }

    /**
     * @return BaseControl
     */
    public function createField(): BaseControl {
        $control = parent::createField();
        $control->setOption('description', _('Pouze pokud je odlišné od "jméno příjmení".'));
        return $control;
    }

    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_ALLOW_BASIC;
    }
}
