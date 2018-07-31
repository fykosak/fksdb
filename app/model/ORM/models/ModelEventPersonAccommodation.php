<?php

class ModelEventPersonAccommodation extends \AbstractModelSingle {

    public function getEventAccommodation() {
        return ModelEventAccommodation::createFromTableRow($this->event_accommodation);
    }
}
