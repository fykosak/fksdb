<?php

namespace FKSDB\ORM\Services\Exception;
use InvalidArgumentException;

/**
 * Class InvalidPostalCode
 */
class InvalidPostalCode extends InvalidArgumentException {

    /**
     * InvalidPostalCode constructor.
     * @param string $postalCode
     * @param null $code
     * @param null $previous
     */
    public function __construct($postalCode, $code = null, $previous = null) {
        parent::__construct(sprintf(_('Invalid postal code %d.'), $postalCode), $code, $previous);
    }
}
