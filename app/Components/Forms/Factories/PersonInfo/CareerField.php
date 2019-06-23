<?php

namespace FKSDB\Components\Forms\Factories\PersonInfo;

use Nette\Forms\Controls\TextArea;

/**
 * Class CareerField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class CareerField extends TextArea {

    public function __construct() {
        parent::__construct(_('Co právě dělá'));
        $this->setOption('description', _('Zobrazeno v seznamu organizátorů'));
    }
}
