<?php

/**
 * Class ModelEventPersonAccommodation
 * @property integer person_id
 * @property integer event_accommodation_id
 * @property integer event_person_accommodation_id
 *
 */
class ModelEventPersonAccommodation extends \AbstractModelSingle {

    public function getEventAccommodation() {
        return ModelEventAccommodation::createFromTableRow($this->event_accommodation);
    }
}
