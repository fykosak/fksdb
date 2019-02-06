<?php

namespace FKSDB\Components\Forms\Controls\PersonAccommodation;

/**
 * Class MultiNightsField
 * @package FKSDB\Components\Forms\Controls\PersonAccommodation
 */
class MultiNightsField extends AccommodationField {
    const RESOLUTION_ID = 'multiNights';

    /**
     * @return string
     */
    public function getMode(): string {
        return self::RESOLUTION_ID;
    }
}
