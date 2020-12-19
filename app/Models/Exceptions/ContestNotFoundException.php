<?php

namespace FKSDB\Models\Exceptions;

use Nette\Http\Response;
use Nette\InvalidStateException;
use Throwable;

class ContestNotFoundException extends InvalidStateException {

    public function __construct(int $contestId, ?Throwable $previous = null) {
        parent::__construct(sprintf(_('Contest %d not found'), $contestId), Response::S404_NOT_FOUND, $previous);
    }
}
