<?php

namespace FKSDB\Components\Forms\Controls\PersonAccommodation;


class Boolean extends AccommodationComponent {
    const RESOLUTION_ID = 'boolean';

    public function getMode() {
        return self::RESOLUTION_ID;
    }
}
