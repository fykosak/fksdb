<?php

namespace FKSDB\Components\Forms\Factories\PersonInfo;

use FKSDB\Components\Forms\Controls\URLTextBox;
use FKSDB\Components\Forms\Factories\AbstractRow;
use Nette\Forms\IControl;

/**
 * Class HomepageField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class HomepageRow extends AbstractRow {
    /**
     * @return string
     */
    public static function getTitle(): string {
        return _('Homepage');
    }

    /**
     * @return IControl
     */
    public function createField(): IControl {
        return new URLTextBox();
    }
    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return 1;
    }

}
