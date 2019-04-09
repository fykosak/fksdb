<?php

namespace FKSDB\Components\Forms\Factories\PersonInfo;

use FKSDB\Components\Forms\Factories\AbstractRow;
use Nette\Forms\Controls\TextArea;
use Nette\Forms\IControl;

/**
 * Class CareerField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class CareerRow extends AbstractRow {

    /**
     * @return string
     */
    public static function getTitle(): string {
        return _('Co právě dělá');
    }

    /**
     * @return IControl
     */
    public function createField(): IControl {
        $control = new TextArea($this->getTitle());
        $control->setOption('description', _('Zobrazeno v seznamu organizátorů'));
        return $control;
    }
    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return 1;
    }
}
