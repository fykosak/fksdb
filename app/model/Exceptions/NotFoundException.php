<?php

namespace FKSDB\Exceptions;

use Nette\Application\BadRequestException;
use Nette\Http\Response;

/**
 * Class NotFoundException
 * @author Michal Červeňák <miso@fykos.cz>
 */
class NotFoundException extends BadRequestException {
    /**
     * NotFoundException constructor.
     * @param string $message
     * @param \Exception|NULL $previous
     */
    public function __construct($message = '', \Exception $previous = null) {
        parent::__construct($message ?: 'Resource no found', Response::S404_NOT_FOUND, $previous);
    }
}
