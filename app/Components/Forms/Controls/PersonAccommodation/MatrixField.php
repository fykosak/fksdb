<?php

namespace FKSDB\Components\Forms\Controls\PersonAccommodation;

/**
 * Class MatrixField
 * @package FKSDB\Components\Forms\Controls\PersonAccommodation
 */
class MatrixField extends AccommodationField {
    const RESOLUTION_ID = 'matrix';

    /**
     * @return string
     */
    public function getMode(): string {
        return self::RESOLUTION_ID;
    }

}
