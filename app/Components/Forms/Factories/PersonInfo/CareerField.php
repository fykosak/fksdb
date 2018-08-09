<?php

namespace FKSDB\Components\Forms\Factories\PersonInfo;

use Nette\Forms\Controls\TextArea;

class CareerField extends TextArea implements \IReactField {
    use \ReactFieldDefinition;

    public function __construct() {
        parent::__construct(_('Co právě dělá'));
        $this->setOption('description', _('Zobrazeno v seznamu organizátorů'));
    }

    public function getReactDefinition(): \ReactField {
        return $this->createReactDefinition();
    }

}
