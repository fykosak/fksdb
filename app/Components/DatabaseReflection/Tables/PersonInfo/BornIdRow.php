<?php

namespace FKSDB\Components\DatabaseReflection\PersonInfo;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\Forms\Controls\WriteOnlyInput;
use FKSDB\Components\Forms\Rules\BornNumber;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;

/**
 * Class BornIdField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class BornIdRow extends AbstractRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Rodné číslo');
    }

    /**
     * @return null|string
     */
    public function getDescription() {
        return _('U cizinců prázdné.');
    }

    /**
     * @return BaseControl
     */
    public function createField(): BaseControl {
        $control = new WriteOnlyInput($this->getTitle());
        $control->setOption('description', $this->getDescription());
        $control->addCondition(Form::FILLED)
            ->addRule(new BornNumber(), _('Rodné číslo nemá platný formát.'));
        return $control;
    }

    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_ALLOW_FULL;
    }
}
