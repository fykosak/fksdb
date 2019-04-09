<?php

namespace FKSDB\Components\Forms\Factories\PersonInfo;

use FKSDB\Components\Forms\Controls\WriteOnlyInput;
use FKSDB\Components\Forms\Factories\AbstractRow;
use Nette\Forms\Form;
use Nette\Forms\IControl;

/**
 * Class UkLoginField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class UkLoginRow extends AbstractRow {
    /**
     * @return string
     */
    public static function getTitle(): string {
        return _('Login UK');
    }

    /**
     * @return IControl
     */
    public function createField(): IControl {
        $control = new WriteOnlyInput($this->getTitle());
        $control->addRule(Form::MAX_LENGTH, null, 8);
        return $control;
    }

    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return 128;
    }

}
