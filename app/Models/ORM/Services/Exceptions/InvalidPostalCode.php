<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services\Exceptions;

class InvalidPostalCode extends \InvalidArgumentException
{

    public function __construct(?string $postalCode, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct(sprintf(_('Invalid postal code %d.'), $postalCode), $code, $previous);
    }
}
