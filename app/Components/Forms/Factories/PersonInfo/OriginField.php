<?php

namespace FKSDB\Components\Forms\Factories\PersonInfo;

use Nette\Forms\Controls\TextArea;

class OriginField extends TextArea implements \IReactField {
    use \ReactFieldDefinition;

    public function __construct() {
        parent::__construct(_('Jak jsi se o nás dozvěděl(a)?'));
    }

    public function getReactDefinition(): \ReactField {
        return $this->createReactDefinition();
    }
}
