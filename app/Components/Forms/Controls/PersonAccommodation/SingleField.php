<?php

namespace FKSDB\Components\Forms\Controls\PersonAccommodation;


class SingleField extends AccommodationField {
    const RESOLUTION_ID = 'single';

    public function getMode(): string {
        return self::RESOLUTION_ID;
    }
}
