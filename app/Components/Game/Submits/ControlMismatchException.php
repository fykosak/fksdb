<?php

declare(strict_types=1);

namespace FKSDB\Components\Game\Submits;

class ControlMismatchException extends TaskCodeException
{
    public function __construct(int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct(_('Wrong code.'), $code, $previous);
    }
}
