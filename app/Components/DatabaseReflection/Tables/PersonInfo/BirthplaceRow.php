<?php

namespace FKSDB\Components\DatabaseReflection\PersonInfo;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\Forms\Controls\WriteOnlyInput;
use Nette\Forms\Controls\BaseControl;
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
    public function getTitle(): string {
        return _('Místo narození');
    }

    /**
     * @return BaseControl
     */
    public function createField(): BaseControl {
        $control = new WriteOnlyInput($this->getTitle());
        $control->setOption('description', _('Město a okres (kvůli diplomům).'));
        $control->addRule(Form::MAX_LENGTH, null, 255);
        return $control;
    }
    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_ALLOW_FULL;
    }
}
