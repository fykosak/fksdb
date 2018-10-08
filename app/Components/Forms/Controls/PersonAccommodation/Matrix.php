<?php

namespace FKSDB\Components\Forms\Controls\PersonAccommodation;

use FKSDB\Components\React\IReactComponent;
use FKSDB\Components\React\ReactField;
use Nette\Forms\Controls\HiddenField;

class Matrix extends AccommodationComponent {
    const RESOLUTION_ID = 'matrix';

    public function getMode() {
        return self::RESOLUTION_ID;
    }

}
