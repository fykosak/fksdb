<?php

namespace FKSDB\Components\Forms\Factories\PersonInfo;

use Nette\Forms\Controls\TextArea;

class NoteField extends TextArea implements \IReactField {
    use \ReactFieldDefinition;

    public function __construct() {
        parent::__construct(_('Poznámka'));
    }

    public function getReactDefinition(): \ReactField {
        return $this->createReactDefinition();
    }

}
