<?php

namespace FKSDB\Components\Forms\Controls\PersonAccommodation;

/**
 * Class SingleField
 * @package FKSDB\Components\Forms\Controls\PersonAccommodation
 */
class SingleField extends AccommodationField {
    const RESOLUTION_ID = 'single';

    /**
     * @return string
     */
    public function getMode(): string {
        return self::RESOLUTION_ID;
    }
}
