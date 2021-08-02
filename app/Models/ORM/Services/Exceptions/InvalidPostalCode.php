<?php

namespace FKSDB\Models\ORM\Services\Exceptions;

class InvalidPostalCode extends \InvalidArgumentException
{

    public function __construct(?string $postalCode, ?int $code = null, ?\Throwable $previous = null)
    {
        parent::__construct(sprintf(_('Invalid postal code %d.'), $postalCode), $code, $previous);
    }
}
