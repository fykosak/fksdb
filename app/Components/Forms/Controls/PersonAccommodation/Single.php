<?php

namespace FKSDB\Components\Forms\Controls\PersonAccommodation;


class Single extends AccommodationField {
    const RESOLUTION_ID = 'boolean';

    public function getMode(): string {
        return self::RESOLUTION_ID;
    }
}
