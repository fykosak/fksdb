<?php

namespace FKSDB\Components\DatabaseReflection\PersonInfo;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\Forms\Controls\WriteOnlyInput;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;

/**
 * Class IdNumberField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class IdNumberRow extends AbstractRow {

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Číslo OP');
    }

    /**
     * @return null|string
     */
    public function getDescription() {
        return _('U cizinců číslo pasu.');
    }

    /**
     * @return BaseControl
     */
    public function createField(): BaseControl {
        $control = new WriteOnlyInput($this->getTitle());
        $control->setOption('description', $this->getDescription());
        $control->addRule(Form::MAX_LENGTH, null, 32);
        return $control;
    }
    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_ALLOW_FULL;
    }

}
