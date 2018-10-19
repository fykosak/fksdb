<?php

namespace FKSDB\Components\Forms\Controls\PersonAccommodation;

class MatrixField extends AccommodationField {
    const RESOLUTION_ID = 'matrix';

    public function getMode(): string {
        return self::RESOLUTION_ID;
    }

}
