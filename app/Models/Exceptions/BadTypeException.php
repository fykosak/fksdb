<?php

declare(strict_types=1);

namespace FKSDB\Models\Exceptions;

use Nette\Application\BadRequestException;
use Nette\Http\IResponse;

class BadTypeException extends BadRequestException
{
    public function __construct(string $expected, $got, ?\Throwable $previous = null)
    {
        parent::__construct(
            \sprintf(_('Expected type %s, got %s.'), $expected, is_object($got) ? \get_class($got) : $got),
            IResponse::S500_INTERNAL_SERVER_ERROR,
            $previous
        );
    }
}
