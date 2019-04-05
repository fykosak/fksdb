<?php

namespace FKSDB\Components\Forms\Factories\PersonInfo;

use Nette\Forms\Controls\TextArea;

/**
 * Class NoteField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class NoteField extends TextArea {

    public function __construct() {
        parent::__construct(_('Poznámka'));
    }
}
