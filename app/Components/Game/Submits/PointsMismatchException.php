<?php

declare(strict_types=1);

namespace FKSDB\Components\Game\Submits;

class PointsMismatchException extends TaskCodeException
{
    public function __construct(string $appendedMessage = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct(_('Points mismatch') . $appendedMessage, $code, $previous);
    }
}
