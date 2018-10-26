<?php

namespace FKSDB\Components\Forms\Factories\Fyziklani\FyziklaniRoom;

use Nette\Forms\Controls\TextInput;

class ColumnsField extends TextInput {
    public function __construct() {
        parent::__construct(_('Number of columns'));
        $this->addRule(\Nette\Forms\Form::INTEGER);
    }
}
