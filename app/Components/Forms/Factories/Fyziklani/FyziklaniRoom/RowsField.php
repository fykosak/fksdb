<?php
namespace FKSDB\Components\Forms\Factories\Fyziklani\FyziklaniRoom;

use Nette\Forms\Controls\TextInput;

class RowsField extends TextInput {
    public function __construct() {
        parent::__construct(_('Number of rows'));
        $this->addRule(\Nette\Forms\Form::INTEGER);
    }
}
