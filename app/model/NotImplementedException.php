<?php

namespace FKSDB;

use Nette\Application\BadRequestException;

/**
 * Class NotImplementedException
 * @package FKSDB
 */
class NotImplementedException extends BadRequestException {
    /**
     * NotImplementedException constructor.
     * @param string $message
     * @param \Exception|NULL $previous
     */
    public function __construct($message = '', \Exception $previous = NULL) {
        parent::__construct($message ?: _('This functionality has not been implemented yet.'), 501, $previous);
    }
}
