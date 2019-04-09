<?php

namespace FKSDB\Components\DatabaseReflection\PersonInfo;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\Forms\Controls\WriteOnlyInput;
use Nette\Forms\Form;
use Nette\Forms\IControl;

/**
 * Class ImField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class ImRow extends AbstractRow {
    /**
     * @return string
     */
    public static function getTitle(): string {
        return _('ICQ, Jabber, apod.');
    }

    /**
     * @return IControl
     */
    public function createField(): IControl {
        $control = new  WriteOnlyInput($this->getTitle());
        $control->addRule(Form::MAX_LENGTH, null, 32);
        return $control;
    }
    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_ALLOW_RESTRICT;
    }

}
