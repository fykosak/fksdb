<?php

namespace FKSDB\Components\Forms\Factories\Org;

use Nette\Forms\Controls\TextInput;

class ContributionField extends TextInput {
    public function __construct() {
        parent::__construct(_('Co udělal'));
        $this->setOption('description', _('Zobrazeno v síni slávy'));
    }
}
