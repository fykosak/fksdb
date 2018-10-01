<?php
namespace FKSDB\Components\Forms\Factories\Address;

use FKSDB\Components\Forms\Controls\WriteOnlyInput;

class CityField extends WriteOnlyInput {
    public function __construct() {
        parent::__construct(_('Město'));
    }
}
