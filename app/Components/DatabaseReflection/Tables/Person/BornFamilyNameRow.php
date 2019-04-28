<?php

namespace FKSDB\Components\DatabaseReflection\Person;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use Nette\Forms\Controls\BaseControl;

/**
 * Class BornFamilyNameRow
 * @package FKSDB\Components\DatabaseReflection\Person
 */
class BornFamilyNameRow extends AbstractRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Born family name');
    }

    /**
     * @return null|string
     */
    public function getDescription() {
        return _('Pouze pokud je odlišné od příjmení.');
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
        return self::PERMISSION_ALLOW_FULL;
    }
}
