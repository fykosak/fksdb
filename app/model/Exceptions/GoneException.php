<?php

namespace FKSDB\Exceptions;

use Nette\Application\BadRequestException;
use Nette\Http\Response;

/**
 * Class GoneException
 * *
 */
class GoneException extends BadRequestException {
    /**
     * GoneException constructor.
     * @param string $message
     * @param \Exception|NULL $previous
     */
    public function __construct($message = '', \Exception $previous = NULL) {
        parent::__construct($message ?: 'Gone', Response::S410_GONE, $previous);
    }
}
