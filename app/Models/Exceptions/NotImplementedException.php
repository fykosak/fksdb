<?php

namespace FKSDB\Models\Exceptions;

use Nette\Application\BadRequestException;
use Nette\Http\Response;

class NotImplementedException extends BadRequestException {

    public function __construct(?string $message = null, ?\Throwable $previous = null) {
        parent::__construct($message ?? _('This functionality has not been implemented yet.'), Response::S501_NOT_IMPLEMENTED, $previous);
    }
}
