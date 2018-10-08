<?php

namespace FKSDB\Components\Forms\Controls\PersonAccommodation;

class MultiNights  extends AccommodationComponent  {
    const RESOLUTION_ID = 'multiNights';
    public function getMode() {
        return self::RESOLUTION_ID;
    }
}
