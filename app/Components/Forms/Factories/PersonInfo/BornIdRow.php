<?php

namespace FKSDB\Components\Forms\Factories\PersonInfo;

use FKSDB\Components\Forms\Controls\WriteOnlyInput;
use FKSDB\Components\Forms\Factories\AbstractRow;
use FKSDB\Components\Forms\Rules\BornNumber;
use Nette\Forms\Form;
use Nette\Forms\IControl;

/**
 * Class BornIdField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class BornIdRow extends AbstractRow {
    /**
     * @return string
     */
    public static function getTitle(): string {
        return _('Rodné číslo');
    }

    /**
     * @return IControl
     */
    public function createField(): IControl {
        $control = new WriteOnlyInput($this->getTitle());
        $control->setOption('description', _('U cizinců prázdné.'));
        $control->addCondition(Form::FILLED)
            ->addRule(new BornNumber(), _('Rodné číslo nemá platný formát.'));
        return $control;
    }
    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return 1024;
    }
}
