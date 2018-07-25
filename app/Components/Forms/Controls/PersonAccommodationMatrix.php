<?php

namespace FKSDB\Components\Forms\Controls;

use Nette\Forms\Controls\HiddenField;

class PersonAccommodationMatrix extends HiddenField {
    const ID = 'person-accommodation-matrix';

    public function __construct() {
        parent::__construct();
        $this->setAttribute('data-id', self::ID);
    }
}
