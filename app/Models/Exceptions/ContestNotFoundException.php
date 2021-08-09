<?php

declare(strict_types=1);

namespace FKSDB\Models\Exceptions;

use Nette\Http\IResponse;
use Nette\InvalidStateException;

class ContestNotFoundException extends InvalidStateException
{

    public function __construct(int $contestId, ?\Throwable $previous = null)
    {
        parent::__construct(sprintf(_('Contest %d not found'), $contestId), IResponse::S404_NOT_FOUND, $previous);
    }
}
