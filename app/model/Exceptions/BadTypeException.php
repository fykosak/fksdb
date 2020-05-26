<?php

namespace FKSDB\Exceptions;

use Nette\Application\BadRequestException;
use Nette\Http\Response;

/**
 * Class BadTypeException
 * *
 */
class BadTypeException extends BadRequestException {
    /**
     * BadTypeException constructor.
     * @param string $expected
     * @param $got
     * @param \Exception|NULL $previous
     */
    public function __construct(string $expected, $got, \Exception $previous = NULL) {
        parent::__construct(\sprintf(_('Expected type %s, got %s.'), $expected, \get_class($got)), Response::S500_INTERNAL_SERVER_ERROR, $previous);
    }
}
