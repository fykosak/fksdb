<?php

namespace FKSDB\Exceptions;

use Nette\Http\Response;
use Nette\InvalidStateException;
use Throwable;

/**
 * Class ContestNotFoundException
 * @package FKSDB\Exceptions
 */
class ContestNotFoundException extends InvalidStateException {
    /**
     * ContestNotFoundException constructor.
     * @param int $contestId
     * @param Throwable|null $previous
     */
    public function __construct(int $contestId, Throwable $previous = null) {
        parent::__construct(sprintf(_('Contest %d not found'), $contestId), Response::S404_NOT_FOUND, $previous);
    }
}
