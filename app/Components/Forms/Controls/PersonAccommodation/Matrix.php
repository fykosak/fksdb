<?php

namespace FKSDB\Components\Forms\Controls\PersonAccommodation;

class Matrix extends AccommodationComponent {
    const RESOLUTION_ID = 'matrix';

    public function getMode() {
        return self::RESOLUTION_ID;
    }

}
