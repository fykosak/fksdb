<?php

declare(strict_types=1);

namespace FKSDB\Models\Exceptions;

use Nette\Application\BadRequestException;
use Nette\Http\IResponse;

class GoneException extends BadRequestException
{
    public function __construct(?string $message = null, ?\Throwable $previous = null)
    {
        parent::__construct($message ?? 'Gone', IResponse::S410_Gone, $previous);
    }
}
