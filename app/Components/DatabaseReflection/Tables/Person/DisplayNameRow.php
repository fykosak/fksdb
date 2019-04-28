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
     * @return null|string
     */
    public function getDescription() {
        return _('Pouze pokud je odlišné od "jméno příjmení".');
    }

    /**
     * @return BaseControl
     */
    public function createField(): BaseControl {
        $control = parent::createField();
        $control->setOption('description', $this->getDescription());
        return $control;
    }

    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_ALLOW_BASIC;
    }
}
