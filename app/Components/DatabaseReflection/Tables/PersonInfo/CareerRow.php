<?php

namespace FKSDB\Components\DatabaseReflection\PersonInfo;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use Nette\Forms\Controls\BaseControl;
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
    public function getTitle(): string {
        return _('Co právě dělá');
    }

    /**
     * @return BaseControl
     */
    public function createField(): BaseControl {
        $control = new TextArea($this->getTitle());
        $control->setOption('description', _('Zobrazeno v seznamu organizátorů'));
        return $control;
    }
    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_ALLOW_BASIC;
    }
}
