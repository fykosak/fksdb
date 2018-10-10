<?php

namespace FKSDB\Components\Forms\Factories\PersonHistory;


use FKSDB\Components\Forms\Controls\Autocomplete\AutocompleteSelectBox;
use FKSDB\Components\Forms\Controls\Autocomplete\SchoolProvider;

class SchoolIdSelectField extends AutocompleteSelectBox {

    public function __construct(SchoolProvider $schoolProvider) {
        parent::__construct(true, _('Škola'));
        $this->setDataProvider($schoolProvider);
        $this->setOption('description', sprintf(_('Pokud nelze školu nalézt, napište na %s.'), 'schola.novum () fykos.cz'));
    }
}
