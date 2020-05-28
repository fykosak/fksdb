<?php

namespace FKSDB\Exceptions;

use Nette\Application\BadRequestException;
use Nette\Http\Response;

/**
 * Class NotImplementedException
 * *
 */
class NotImplementedException extends BadRequestException {
    /**
     * NotImplementedException constructor.
     * @param string $message
     * @param \Exception|NULL $previous
     */
    public function __construct($message = '', \Exception $previous = NULL) {
        parent::__construct($message ?: _('This functionality has not been implemented yet.'), Response::S501_NOT_IMPLEMENTED, $previous);
    }
}
