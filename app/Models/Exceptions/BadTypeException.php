<?php

namespace FKSDB\Models\Exceptions;

use Nette\Application\BadRequestException;
use Nette\Http\Response;

/**
 * Class BadTypeException
 * @author Michal Červeňák <miso@fykos.cz>
 */
class BadTypeException extends BadRequestException {

    public function __construct(string $expected, ?object $got, ?\Throwable $previous = null) {
        parent::__construct(\sprintf(_('Expected type %s, got %s.'), $expected, \get_class($got)), Response::S500_INTERNAL_SERVER_ERROR, $previous);
    }
}
