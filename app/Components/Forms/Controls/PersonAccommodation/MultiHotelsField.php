<?php

namespace FKSDB\Components\Forms\Controls\PersonAccommodation;

/**
 * Class MultiHotelsField
 * @package FKSDB\Components\Forms\Controls\PersonAccommodation
 */
class MultiHotelsField extends AccommodationField {
    const RESOLUTION_ID = 'multiHotels';

    /**
     * @return string
     */
    public function getMode(): string {
        return self::RESOLUTION_ID;
    }
}
