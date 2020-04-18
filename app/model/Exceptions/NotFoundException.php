<?php

namespace FKSDB\Exceptions;

use Nette\Application\BadRequestException;
use Nette\Http\Response;

/**
 * Class NotFoundException
 * @package FKSDB\Exceptions
 */
class NotFoundException extends BadRequestException {
    /**
     * NotFoundException constructor.
     * @param string $message
     * @param \Exception|NULL $previous
     */
    public function __construct($message = '', \Exception $previous = NULL) {
        parent::__construct($message ?: 'Page no found', Response::S404_NOT_FOUND, $previous);
    }
}
