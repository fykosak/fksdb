<?php

namespace FKSDB\Components\Forms\Factories\PersonInfo;

use FKSDB\Components\Forms\Controls\WriteOnlyDatePicker;
use FKSDB\Components\Forms\Factories\AbstractRow;
use Nette\Forms\IControl;

/**
 * Class BornField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class BornRow extends AbstractRow {
    /**
     * @return string
     */
    public static function getTitle(): string {
        return _('Datum narození');
    }

    /**
     * @return IControl
     */
    public function createField(): IControl {
        $control = new WriteOnlyDatePicker($this->getTitle());
        $control->setDefaultDate((new \DateTime())->modify('-16 years'));
        return $control;
    }
    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return 1024;
    }
}
