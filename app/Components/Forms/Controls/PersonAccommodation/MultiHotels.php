<?php

namespace FKSDB\Components\Forms\Controls\PersonAccommodation;


class MultiHotels extends AccommodationComponent {
    const RESOLUTION_ID = 'multiHotels';

    public function getMode() {
        return self::RESOLUTION_ID;
    }
}
