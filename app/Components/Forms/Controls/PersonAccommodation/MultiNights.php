<?php

namespace FKSDB\Components\Forms\Controls\PersonAccommodation;

class MultiNights extends AccommodationField {
    const RESOLUTION_ID = 'multiNights';

    public function getMode(): string {
        return self::RESOLUTION_ID;
    }
}
