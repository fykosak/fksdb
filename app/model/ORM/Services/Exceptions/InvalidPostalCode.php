<?php

namespace FKSDB\ORM\Services\Exception;

use InvalidArgumentException;

/**
 * Class InvalidPostalCode
 */
class InvalidPostalCode extends InvalidArgumentException {

    public function __construct(?string $postalCode, ?int $code = null, ?\Throwable $previous = null) {
        parent::__construct(sprintf(_('Invalid postal code %d.'), $postalCode), $code, $previous);
    }
}
