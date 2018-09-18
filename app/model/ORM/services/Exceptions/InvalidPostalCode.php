<?php

class InvalidPostalCode extends InvalidArgumentException {

    public function __construct($postalCode, $code = null, $previous = null) {
        parent::__construct(sprintf(_('Invalid postal code %d.'), $postalCode), $code, $previous);
    }
}
