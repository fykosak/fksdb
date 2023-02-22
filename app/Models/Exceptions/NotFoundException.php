<?php

declare(strict_types=1);

namespace FKSDB\Models\Exceptions;

use Nette\Application\BadRequestException;
use Nette\Http\IResponse;

class NotFoundException extends BadRequestException
{

    public function __construct(?string $message = '', ?\Throwable $previous = null)
    {
        parent::__construct($message ?? 'Resource no found', IResponse::S404_NotFound, $previous);
    }
}
