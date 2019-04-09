<?php

namespace FKSDB\Components\Forms\Factories\PersonInfo;

use FKSDB\Components\Forms\Controls\WriteOnlyInput;
use FKSDB\Components\Forms\Factories\AbstractRow;
use Nette\Forms\Form;
use Nette\Forms\IControl;

/**
 * Class BirthplaceField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class BirthplaceRow extends AbstractRow {
    /**
     * @return string
     */
    public static function getTitle(): string {
        return _('Místo narození');
    }

    /**
     * @return IControl
     */
    public function createField(): IControl {
        $control = new WriteOnlyInput($this->getTitle());
        $control->setOption('description', _('Město a okres (kvůli diplomům).'));
        $control->addRule(Form::MAX_LENGTH, null, 255);
        return $control;
    }
    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return 512;
    }
}
