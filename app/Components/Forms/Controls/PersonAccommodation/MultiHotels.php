<?php

namespace FKSDB\Components\Forms\Controls\PersonAccommodation;


class MultiHotels extends AccommodationField {
    const RESOLUTION_ID = 'multiHotels';

    public function getMode(): string {
        return self::RESOLUTION_ID;
    }
}
