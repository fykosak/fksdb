<?php

namespace FKSDB\Components\Forms\Controls\PersonAccommodation;

class MultiNightsField extends AccommodationField {
    const RESOLUTION_ID = 'multiNights';

    public function getMode(): string {
        return self::RESOLUTION_ID;
    }
}
