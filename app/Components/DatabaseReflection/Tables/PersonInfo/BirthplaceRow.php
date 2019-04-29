<?php

namespace FKSDB\Components\DatabaseReflection\PersonInfo;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\Forms\Controls\WriteOnlyInput;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;

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
     * @return null|string
     */
    public function getDescription() {
        return _('Město a okres (kvůli diplomům).');
    }

    /**
     * @return BaseControl
     */
    public function createField(): BaseControl {
        $control = new WriteOnlyInput($this->getTitle());
        $control->setOption('description', $this->getDescription());
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
