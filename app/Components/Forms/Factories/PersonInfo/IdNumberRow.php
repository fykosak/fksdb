<?php

namespace FKSDB\Components\Forms\Factories\PersonInfo;

use FKSDB\Components\Forms\Controls\WriteOnlyInput;
use FKSDB\Components\Forms\Factories\AbstractRow;
use Nette\Forms\Form;
use Nette\Forms\IControl;

/**
 * Class IdNumberField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class IdNumberRow extends AbstractRow {

    /**
     * @return string
     */
    public static function getTitle(): string {
        return _('Číslo OP');
    }

    /**
     * @return IControl
     */
    public function createField(): IControl {
        $control = new WriteOnlyInput($this->getTitle());
        $control->setOption('description', _('U cizinců číslo pasu.'));
        $control->addRule(Form::MAX_LENGTH, null, 32);
        return $control;
    }
    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return 1024;
    }

}
