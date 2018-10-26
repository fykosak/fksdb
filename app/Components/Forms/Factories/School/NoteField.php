<?php
namespace FKSDB\Components\Forms\Factories\School;


use Nette\Forms\Controls\TextInput;

class NoteField extends TextInput {
    public function __construct() {
        parent::__construct(_('Poznámka'));
    }
}
