<?php

namespace FKSDB\Model\Exceptions;

use Nette\Application\BadRequestException;
use Nette\Http\Response;

/**
 * Class GoneException
 * @author Michal Červeňák <miso@fykos.cz>
 */
class GoneException extends BadRequestException {

    public function __construct(?string $message = null, ?\Throwable $previous = null) {
        parent::__construct($message ?: 'Gone', Response::S410_GONE, $previous);
    }
}
