<?php

namespace FKSDB\Components\Forms\Factories\PersonInfo;

use Nette\Forms\Controls\TextArea;

class NoteField extends TextArea {

    public function __construct() {
        parent::__construct(_('Poznámka'));
    }
}
